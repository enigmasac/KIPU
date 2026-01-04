<?php

namespace App\Jobs\Document;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use App\Jobs\Common\CreateItem;
use App\Models\Document\Document;
use App\Models\Document\DocumentTotal;
use App\Models\Setting\Tax;
use App\Traits\Currencies;
use App\Traits\DateTime;
use Illuminate\Support\Str;

class CreateDocumentItemsAndTotals extends Job implements HasOwner, HasSource, ShouldCreate
{
    use Currencies, DateTime;

    protected $document;

    public function __construct(Document $document, $request)
    {
        $this->document = $document;

        parent::__construct($request);
    }

    public function handle(): void
    {
        $precision = currency($this->document->currency_code)->getPrecision();

        list($sub_total, $actual_total, $discount_amount_total, $taxes) = $this->createItems();

        $sort_order = 1;

        // Add sub total
        DocumentTotal::create([
            'company_id' => $this->document->company_id,
            'type' => $this->document->type,
            'document_id' => $this->document->id,
            'code' => 'sub_total',
            'name' => 'invoices.sub_total',
            'amount' => round($sub_total, $precision),
            'sort_order' => $sort_order,
            'created_from' => $this->request['created_from'],
            'created_by' => $this->request['created_by'],
        ]);

        $this->request['amount'] += $actual_total;

        $sort_order++;

        // Add discount
        if ($discount_amount_total > 0) {
            DocumentTotal::create([
                'company_id' => $this->document->company_id,
                'type' => $this->document->type,
                'document_id' => $this->document->id,
                'code' => 'item_discount',
                'name' => 'invoices.item_discount',
                'amount' => round($discount_amount_total, $precision),
                'sort_order' => $sort_order,
                'created_from' => $this->request['created_from'],
                'created_by' => $this->request['created_by'],
            ]);

            $sort_order++;
        }

        if (! empty($this->request['discount'])) {
            if ($this->request['discount_type'] === 'percentage') {
                //$discount_total = ($sub_total - $discount_amount_total) * ($this->request['discount'] / 100);
                $discount_total = $sub_total * ($this->request['discount'] / 100);
            } else {
                $discount_total = $this->request['discount'];
            }

            DocumentTotal::create([
                'company_id' => $this->document->company_id,
                'type' => $this->document->type,
                'document_id' => $this->document->id,
                'code' => 'discount',
                'name' => 'invoices.discount',
                'amount' => round($discount_total, $precision),
                'sort_order' => $sort_order,
                'created_from' => $this->request['created_from'],
                'created_by' => $this->request['created_by'],
            ]);

            $sort_order++;
        }

        // Add taxes
        if (! empty($taxes)) {
            $sorted_taxes = collect($taxes)
                ->map(function ($tax, $tax_id) {
                    $priority = optional(Tax::find($tax_id))->priority ?? 0;

                    return [
                        'tax_id'   => $tax_id,
                        'name'     => $tax['name'],
                        'amount'   => $tax['amount'],
                        'priority' => $priority,
                    ];
                })
                ->values()
                ->sort(function ($a, $b) {
                    if ($a['priority'] === $b['priority']) {
                        return strcmp($a['name'], $b['name']);
                    }

                    return $a['priority'] <=> $b['priority'];
                });

            foreach ($sorted_taxes as $tax) {
                DocumentTotal::create([
                    'company_id' => $this->document->company_id,
                    'type' => $this->document->type,
                    'document_id' => $this->document->id,
                    'code' => 'tax',
                    'name' => Str::ucfirst($tax['name']),
                    'amount' => round(abs($tax['amount']), $precision),
                    'sort_order' => $sort_order,
                    'created_from' => $this->request['created_from'],
                    'created_by' => $this->request['created_by'],
                ]);

                $this->request['amount'] += $tax['amount'];

                $sort_order++;
            }
        }

        // Add extra totals, i.e. shipping fee
        if (! empty($this->request['totals'])) {
            foreach ($this->request['totals'] as $total) {
                $total['company_id'] = $this->document->company_id;
                $total['type'] = $this->document->type;
                $total['document_id'] = $this->document->id;
                $total['sort_order'] = $sort_order;
                $total['created_from'] = $this->request['created_from'];
                $total['created_by'] = $this->request['created_by'];

                if (empty($total['code'])) {
                    $total['code'] = 'extra';
                }

                $total['amount'] = round(abs($total['amount']), $precision);

                DocumentTotal::create($total);

                if (empty($total['operator']) || ($total['operator'] == 'addition')) {
                    $this->request['amount'] += $total['amount'];
                } else {
                    // subtraction
                    $this->request['amount'] -= $total['amount'];
                }

                $sort_order++;
            }
        }

        $this->request['amount'] = round($this->request['amount'], $precision);

        // Add total
        DocumentTotal::create([
            'company_id' => $this->document->company_id,
            'type' => $this->document->type,
            'document_id' => $this->document->id,
            'code' => 'total',
            'name' => 'invoices.total',
            'amount' => $this->request['amount'],
            'sort_order' => $sort_order,
            'created_from' => $this->request['created_from'],
            'created_by' => $this->request['created_by'],
        ]);

        $is_invoice = $this->document->type === 'invoice';
        $is_credit_note = $this->document->type === Document::CREDIT_NOTE_TYPE;
        $is_debit_note = $this->document->type === Document::DEBIT_NOTE_TYPE;

        // VERIFICACIÓN PARA NOTAS DE CRÉDITO (SUNAT)
        if ($is_credit_note && !empty($this->document->invoice_id)) {
            $invoice = Document::find($this->document->invoice_id);
            if ($invoice) {
                $already_credited = Document::where('invoice_id', $invoice->id)
                    ->where('type', 'credit-note')
                    ->where('id', '!=', $this->document->id)
                    ->sum('amount');
               
                $total_credit = round($already_credited + $this->request['amount'], $precision);
                $invoice_amount = round($invoice->amount, $precision);

                if ($total_credit > $invoice_amount) {
                    throw new \Exception("El monto acumulado de notas de crédito (" . $total_credit . ") excede el monto de la factura original (" . $invoice_amount . ").");
                }
            }
        }

        // LOGICA DE DETRACCIÓN (SUNAT)
        if (($is_invoice && $this->request['amount'] >= 700) || ($is_credit_note && !empty($this->document->invoice_id)) || ($is_debit_note && !empty($this->document->invoice_id))) {
            $detraction_amount = 0;
            $max_percentage = 0;
            $has_detraction = false;

            if ($is_invoice) {
                // Obtenemos los items directamente de la BD para asegurar que tenemos los campos de detracción
                $document_items = \App\Models\Document\DocumentItem::where('document_id', $this->document->id)->get();

                foreach ($document_items as $document_item) {
                    $product = \App\Models\Common\Item::find($document_item->item_id);
                    
                    if ($product && $product->is_detraction) {
                        $has_detraction = true;
                        // Según SUNAT, si hay varios, se aplica el mayor porcentaje sobre el total de la operación
                        if ($product->detraction_percentage > $max_percentage) {
                            $max_percentage = (float) $product->detraction_percentage;
                        }
                    }
                }
            } else {
                // Es nota de crédito, heredamos si la factura original tenía
                $invoice_detraction = DocumentTotal::where('document_id', $this->document->invoice_id)
                    ->where('code', 'detraction')
                    ->first();
                
                if ($invoice_detraction) {
                    $has_detraction = true;
                    if (preg_match('/(\d+(\.\d+)?)%/', $invoice_detraction->name, $matches)) {
                        $max_percentage = (float) $matches[1];
                    }
                }
            }

            if ($has_detraction && $max_percentage > 0) {
                $detraction_amount = (float) $this->request['amount'] * ($max_percentage / 100);
                
                $sort_order++;

                DocumentTotal::create([
                    'company_id' => $this->document->company_id,
                    'type' => $this->document->type,
                    'document_id' => $this->document->id,
                    'code' => 'detraction',
                    'name' => 'Detracción (' . $max_percentage . '%)',
                    'amount' => round($detraction_amount, $precision),
                    'sort_order' => $sort_order,
                    'created_from' => $this->request['created_from'],
                    'created_by' => $this->request['created_by'],
                ]);

                // Guardar info SPOT en notas del documento
                $bn_account = setting('company.sunat_bn_account', 'No configurada');
                
                $spot_note = "\n\nOPERACIÓN SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS (SPOT)\n";
                $spot_note .= "Cuenta Banco de la Nación: " . $bn_account . "\n";
                $spot_note .= "Monto Detracción: " . number_format($detraction_amount, $precision) . " " . $this->document->currency_code . "\n";
                
                if ($is_credit_note) {
                    $spot_note = "\n\nAJUSTE DE DETRACCIÓN POR NOTA DE CRÉDITO\n" . $spot_note;
                } elseif ($is_debit_note) {
                    $spot_note = "\n\nAJUSTE DE DETRACCIÓN POR NOTA DE DÉBITO\n" . $spot_note;
                }

                // Actualizar notas sin causar bucle
                \DB::table('documents')->where('id', $this->document->id)->update([
                    'notes' => $this->document->notes . $spot_note
                ]);
            }
        }
    }

    protected function createItems(): array
    {
        $sub_total = $actual_total = $discount_amount = $discount_amount_total = 0;

        $taxes = [];

        if (empty($this->request['items'])) {
            return [$sub_total, $actual_total, $discount_amount_total, $taxes];
        }

        if (! empty($this->request['discount']) && $this->request['discount_type'] !== 'percentage') {
            $for_fixed_discount = $this->fixedDiscountCalculate();
        }

        foreach ((array) $this->request['items'] as $key => $item) {
            $item['global_discount'] = 0;

            // Disable this lines for global discount issue fixed ( https://github.com/akaunting/akaunting/issues/2797 )
            if (! empty($this->request['discount'])) {
                if (isset($for_fixed_discount)) {
                    $item['global_discount'] = ($for_fixed_discount[$key] / ($for_fixed_discount['total'] / 100)) * ($this->request['discount'] / 100);
                    $item['global_discount_type'] = $this->request['discount_type'];
                } else {
                    $item['global_discount'] = $this->request['discount'];
                    $item['global_discount_type'] = $this->request['discount_type'];
                }
            }

            $item['created_from'] = $this->request['created_from'];
            $item['created_by'] = $this->request['created_by'];

            if (empty($item['item_id'])) {
                $new_item_request = [
                    'company_id' => $this->request['company_id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? '',
                    'sunat_unit_code' => $item['sunat_unit_code'] ?? 'NIU',
                    'sunat_tax_type' => $item['sunat_tax_type'] ?? '10',
                    'description' => $item['description'],
                    'sale_price' => $item['price'],
                    'purchase_price' => $item['price'],
                    'created_from' => $item['created_from'],
                    'created_by' => $item['created_by'],
                    'enabled' => '1',
                ];

                if (! empty($item['tax_ids'])) {
                    $new_item_request['tax_ids'] = $item['tax_ids'];
                }

                $new_item = $this->dispatch(new CreateItem($new_item_request));

                $item['item_id'] = $new_item->id;
            }

            $document_item = $this->dispatch(new CreateDocumentItem($this->document, $item));

            # This line changed for discount calcualter issue
            //$item_amount = (double) $item['price'] * (double) $item['quantity'];
            $item_amount = $document_item->total;

            $discount_amount = 0;

            if (! empty($item['discount'])) {
                if ($item['discount_type'] === 'percentage') {
                    $discount_amount = ($item_amount * ($item['discount'] / 100));
                } else {
                    $discount_amount = $item['discount'];
                }
            }

            // Calculate totals
            $sub_total += $item_amount;
            $actual_total += $document_item->total;

            $discount_amount_total += $discount_amount;

            if (! $document_item->item_taxes) {
                continue;
            }

            // Set taxes
            foreach ((array) $document_item->item_taxes as $item_tax) {
                if (array_key_exists($item_tax['tax_id'], $taxes)) {
                    $taxes[$item_tax['tax_id']]['amount'] += round((float) $item_tax['amount'], $this->document->currency->precision);
                } else {
                    $taxes[$item_tax['tax_id']] = [
                        'name' => $item_tax['name'],
                        'amount' => round((float) $item_tax['amount'], $this->document->currency->precision),
                    ];
                }
            }
        }

        // Disable this lines for global discount issue fixed ( https://github.com/akaunting/akaunting/issues/2797 )
        if (! empty($this->request['discount'])) {
            if ($this->request['discount_type'] === 'percentage') {
                $actual_total -= ($sub_total * ($this->request['discount'] / 100));
            } else {
                $actual_total -= $this->request['discount'];
            }
        }

        return [$sub_total, $actual_total, $discount_amount_total, $taxes];
    }

    public function fixedDiscountCalculate()
    {
        $total = 0;

        foreach ((array) $this->request['items'] as $item) {
            $sub = (double) $item['price'] * (double) $item['quantity'];

            if (! empty($this->request['discount'])) {
                if (isset($item['discount']) && isset($item['discount_type'])) {
                    if ($item['discount_type'] === 'percentage') {
                        $sub -= ($sub * ($item['discount'] / 100));
                    } else {
                        $sub -= $item['discount'];
                    }
                }
            }

            $total += $sub;
            $item_total[] = $sub;
        }

        $item_total['total'] = $total;

        return $item_total;
    }
}
