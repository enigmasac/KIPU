<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Inventory\Jobs\Variants\CreateVariantValue;
use Modules\Inventory\Jobs\Variants\UpdateVariantValue;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Models\VariantValue;
use Modules\Woocommerce\Http\Resources\Module\InventoryVariantValue;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Throwable;

class SyncAttributeTerm extends Job
{
    protected $term;

    public function __construct($term)
    {
        $this->term = $term;

        parent::__construct($term);
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
                'woocommerce_id' => empty($this->term->id) ? 0 : $this->term->id,
                'item_type'      => VariantValue::class,
            ];

            if (! empty($this->term->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (array) (new InventoryVariantValue($this->term))->jsonSerialize();

            $request = request()->merge($data);

            if ($integration->exists && null !== $integration->item) {
                $integration->item->fill($data);

                if ($integration->item->isDirty()) {
                    $this->dispatch((new UpdateVariantValue($integration->item, $request)));

                    $integration->save();
                }
            } else {
                $attributeIntegration = WooCommerceIntegration::where(
                    [
                        'company_id'     => company_id(),
                        'woocommerce_id' => $this->term->attribute_id,
                        'item_type'      => Variant::class,
                    ]
                )->first();

                $request->merge(['variant_id' => $attributeIntegration->item_id]);
                $item = $this->dispatch((new CreateVariantValue($request)));

                $integration->item_id              = $item->id;
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
