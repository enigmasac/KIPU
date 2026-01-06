<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Http\Requests\Setting\Category as CategoryRequest;
use App\Jobs\Setting\CreateCategory;
use App\Jobs\Setting\UpdateCategory;
use App\Models\Setting\Category;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Woocommerce\Http\Resources\Setting\Category as SettingCategory;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Throwable;

class SyncCategory extends Job
{
    protected $category;

    public function __construct($category)
    {
        $this->category = $category;

        parent::__construct($category);
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
                'woocommerce_id' => empty($this->category->id) ? 0 : $this->category->id,
                'item_type'      => Category::class,
            ];

            //woocommerce old data control
            $_category = Category::where(['name' => $this->category->name, 'type' => 'item'])->first();

            if (! empty($_category)) {
                $integration_params['item_id'] = $_category->id;
            }

            if (! empty($this->category->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (array) (new SettingCategory($this->category))->jsonSerialize();

            $item = Category::getWithoutChildren()->find($integration->item_id);

            if ($integration->exists && null !== $item) {
                $item->fill($data);

                if ($item->isDirty()) {
                    $this->dispatch((new UpdateCategory($item, (new CategoryRequest())->merge($data))));

                    $integration->save();
                }
            } else {
                $data['color'] = '#' . dechex(random_int(0x000000, 0xFFFFFF));

                $akauntingCategory = $this->dispatch((new CreateCategory((new CategoryRequest())->merge($data))));

                $integration->item_id              = $akauntingCategory->id;
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
