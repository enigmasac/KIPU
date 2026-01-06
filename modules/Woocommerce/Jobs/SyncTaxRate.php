<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Http\Requests\Setting\Tax as TaxRequest;
use App\Jobs\Setting\CreateTax;
use App\Jobs\Setting\UpdateTax;
use App\Models\Setting\Tax;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Woocommerce\Http\Resources\Setting\Tax as SettingTax;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Throwable;

class SyncTaxRate extends Job
{
    protected $taxRate;

    public function __construct($taxRate)
    {
        $this->taxRate = $taxRate;

        parent::__construct($taxRate);
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
                'woocommerce_id' => empty($this->taxRate->id) ? 0 : $this->taxRate->id,
                'item_type'      => Tax::class,
            ];

            //woocommerce old data control
            $_category = Tax::where('name', $this->taxRate->name)->first();

            if (! empty($_category)) {
                $integration_params['item_id'] = $_category->id;
            }

            if (! empty($this->taxRate->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (array) (new SettingTax($this->taxRate))->jsonSerialize();

            if ($integration->exists && null !== $integration->item) {
                $integration->item->fill($data);

                if ($integration->item->isDirty()) {
                    $this->dispatch((new UpdateTax($integration->item, (new TaxRequest())->merge($data))));

                    $integration->save();
                }
            } else {
                $tax = $this->dispatch((new CreateTax((new TaxRequest())->merge($data))));

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
