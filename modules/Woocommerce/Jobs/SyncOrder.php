<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Http\Requests\Banking\Transaction as TransactionRequest;
use App\Http\Requests\Document\Document as InvoiceRequest;
use App\Http\Requests\Setting\Currency as CurrencyRequest;
use App\Jobs\Banking\CreateBankingDocumentTransaction;
use App\Jobs\Banking\UpdateTransaction;
use App\Jobs\Document\CreateDocument;
use App\Jobs\Document\UpdateDocument;
use App\Jobs\Setting\CreateCurrency;
use App\Models\Document\Document;
use App\Models\Setting\Tax;
use App\Traits\Jobs;
use App\Traits\Modules;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Woocommerce\Http\Resources\Banking\Transaction;
use Modules\Woocommerce\Http\Resources\Income\Invoice;
use Modules\Woocommerce\Http\Resources\Income\InvoiceItems;
use Modules\Woocommerce\Http\Resources\Income\InvoiceTotals;
use Modules\Woocommerce\Http\Resources\Module\CustomFields;
use Modules\Woocommerce\Http\Resources\Setting\Currency;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use stdClass;
use Throwable;

class SyncOrder extends Job
{
    use Jobs;
    use Modules;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;

        parent::__construct($order);
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $integration_params = [
                'company_id'     => company_id(),
                'woocommerce_id' => empty($this->order->id) ? 0 : $this->order->id,
                'item_type'      => Document::class,
            ];

            //woocommerce old data control
            $_invoice = Document::where('order_number', $this->order->number)->first();

            if (! empty($_invoice)) {
                $integration_params['item_id'] = $_invoice->id;
            }

            if (! empty($this->order->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $customer          = new stdClass();
            $customer->id      = $this->order->customer_id;
            $customer->name = collect([
                                          $this->order->billing->first_name,
                                          $this->order->billing->last_name,
                                      ])->filter()->implode(' ');
            $customer->email   = $this->order->billing->email;
            $customer->status  = 1;
            $customer->phone   = $this->order->billing->phone;
            $customer->address = collect(
                [
                    $this->order->billing->address_1,
                    $this->order->billing->address_2,
                    $this->order->billing->city,
                    $this->order->billing->postcode,
                    $this->order->billing->country,
                ]
            )->filter()->implode("\r\n");
            $customer_id       = $this->dispatchSync(new SyncContact($customer));

            $this->syncCurrency($this->order);

            $data = (array) (new Invoice($this->order))->jsonSerialize();

            $this->applyOrderTotalTaxesToProducts($this->order);

            $data['contact_id'] = $customer_id;

            foreach ($this->order->totalTaxes as $tax) {
                $data['totals'][] = (new InvoiceTotals((object) $tax))->jsonSerialize();
            }

            foreach ($this->order->line_items as $line_item) {
                $data['items'][] = (new InvoiceItems((object) $line_item))->jsonSerialize();
            }

            if ($this->moduleIsEnabled('custom-fields')) {
                $customFields = (array) (new CustomFields($this->order->meta_data))->jsonSerialize();

                $data = array_merge($data, $customFields);

                request()->merge($data);
            }

            if (null !== $integration->item) {
                $data['document_number'] = $integration->item->document_number;
                $integration->item->fill($data);

                if ($integration->item->isDirty()) {
                    $invoice = $this->dispatch(
                        (new UpdateDocument($integration->item, (new InvoiceRequest())->merge($data)))
                    );

                    $integration->save();
                }
            } else {
                $invoice = $this->dispatch((new CreateDocument((new InvoiceRequest())->merge($data))));

                $integration->item_id              = $invoice->id;
                $integration->save();
            }

            if (false === isset($invoice)) {
                return;
            }

            $this->order->invoice_id    = $invoice->id;
            $this->order->currency_rate = $invoice->currency_rate;
            $transactionData            = (array) (new Transaction($this->order))->jsonSerialize();

            if ($invoice->transactions()->first()) {
                $this->dispatch(
                    (new UpdateTransaction(
                        $invoice->transactions()->first(),
                        (new TransactionRequest())->merge($transactionData)
                    ))
                );
            } else {
                try {
                    $this->dispatch(
                        (new CreateBankingDocumentTransaction(
                            $invoice,
                            (new TransactionRequest())->merge($transactionData)
                        ))
                    );
                } catch (Exception $e) {
                    $data['totals'][] = [
                        'amount'     => $this->order->total - $invoice->amount,
                        'code'       => 'correction',
                        'name'       => trans('woocommerce::general.amount_correction'),
                        'company_id' => company_id(),
                        'sort_order' => 99,
                    ];

                    $invoice = $this->dispatch((new UpdateDocument($invoice, (new InvoiceRequest())->merge($data))));

                    $this->dispatch(
                        (new CreateBankingDocumentTransaction(
                            $invoice,
                            (new TransactionRequest())->merge($transactionData)
                        ))
                    );
                }
            }

            DB::commit();
        } catch (JsonException | Throwable $e) {
            Log::error(
                'WC Integration::: Exception:' . basename($e->getFile()) . ':' . $e->getLine() . ' - '
                . $e->getCode() . ': ' . $e->getMessage()
            );

            report($e);

            DB::rollBack();

            throw new Exception($e);
        }
    }

    private function syncCurrency($order)
    {
        if (null !== config("money.$order->currency.rate")) {
            return;
        }

        $data = (array) (new Currency($order))->jsonSerialize();

        $currency = $this->dispatch((new CreateCurrency((new CurrencyRequest())->merge($data))));

        config()->set("money.$order->currency.rate", $currency->rate);
    }


    private function applyOrderTotalTaxesToProducts(&$order)
    {
        $totalTaxes = [];
        foreach ($order->tax_lines as $key => $total) {
            $tax = Tax::where('name', $total->label)->first();

            if (null === $tax) {
                break;
            }

            foreach ($order->tax_lines as $items) {
                $items->tax_id[] = $tax->id;
            }
            //

            $totalTaxes[] = [
                'amount'     => $total->shipping_tax_total + $total->tax_total,
                'code'       => 'tax',
                'name'       => $total->label,
                'company_id' => company_id(),
                'sort_order' => 1,
            ];

            unset($order->tax_lines[$key]);
        }

        // Totals (shipping, fees etc) except taxes, total and sub-total
        foreach ($order->shipping_lines as $total) {
            $totalTaxes[] = [
                'amount'     => $total->total,
                'code'       => $total->method_id,
                'name'       => $total->method_title,
                'company_id' => company_id(),
                'sort_order' => 1,
            ];
        }

        foreach ($order->fee_lines as $total) {
            $totalTaxes[] = [
                'amount'     => $total->total,
                'code'       => 'fee',
                'name'       => $total->name,
                'company_id' => company_id(),
                'sort_order' => 1,
            ];
        }


        $order->totalTaxes = $totalTaxes;
    }
}
