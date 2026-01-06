<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Http\Requests\Banking\Account as AccountRequest;
use App\Jobs\Banking\CreateAccount;
use App\Jobs\Banking\UpdateAccount;
use App\Models\Banking\Account;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Woocommerce\Http\Resources\Banking\Account as BankingAccount;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Throwable;

class SyncPaymentMethod extends Job
{
    protected $paymentMethod;

    public function __construct($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        parent::__construct($paymentMethod);
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
                'woocommerce_id' => empty($this->paymentMethod->order) ? 0 : $this->paymentMethod->order,
                // does not have an id :(
                'item_type'      => Account::class,
            ];

            //woocommerce old data control
            $_paymentMethod = Account::where(['name' => $this->paymentMethod->method_title, 'number' => $this->paymentMethod->id])->first();

            if (! empty($_paymentMethod)) {
                $integration_params['item_id'] = $_paymentMethod->id;
            }

            if (! empty($this->paymentMethod->order)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (new BankingAccount($this->paymentMethod))->jsonSerialize();

            if (null !== $integration->item) {
                $integration->item->fill($data);

                if ($integration->item->isDirty()) {
                    $this->dispatch((new UpdateAccount($integration->item, (new AccountRequest())->merge($data))));

                    $integration->save();
                }
            } else {
                $tax = $this->dispatch((new CreateAccount((new AccountRequest())->merge($data))));

                $integration->item_id              = $tax->id;
                $integration->save();
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
}
