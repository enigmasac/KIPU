<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Inventory\Http\Requests\Variant as VariantRequest;
use Modules\Inventory\Jobs\Variants\CreateVariant;
use Modules\Inventory\Jobs\Variants\UpdateVariant;
use Modules\Inventory\Models\Variant;
use Modules\Woocommerce\Http\Resources\Module\InventoryVariant;
use Modules\Woocommerce\Models\WooCommerceIntegration;

use Throwable;

class SyncAttribute extends Job
{
    protected $attribute;

    public function __construct($attribute)
    {
        $this->attribute = $attribute;

        parent::__construct($attribute);
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
                'woocommerce_id' => empty($this->attribute->id) ? 0 : $this->attribute->id,
                'item_type'      => Variant::class,
            ];

            if (! empty($this->attribute->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (array) (new InventoryVariant($this->attribute))->jsonSerialize();

            request()->merge($data);

            $request = (new VariantRequest())->merge($data);

            if ($integration->exists && null !== $integration->item) {
                $integration->item->fill($data);

                if ($integration->item->isDirty()) {
                    request()->merge(['items' => $integration->item->values->toArray()]);

                    $request->merge(['items' => $integration->item->values->toArray()]);

                    $this->dispatch((new UpdateVariant($integration->item, $request)));

                    $integration->save();
                }
            } else {
                $item = $this->dispatch((new CreateVariant($request)));

                $integration->item_id = $item->id;
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
