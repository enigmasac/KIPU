<?php

namespace Modules\Woocommerce\Observers\Inventory;

use App\Abstracts\Observer;
use App\Traits\Modules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\ItemGroup;
use Modules\Inventory\Models\ItemGroupItem as Model;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Models\VariantValue;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class ItemGroupItem extends Observer
{
    use Modules;

    /**
     * @var WooCommerceAdapter
     */
    private $adapter;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->adapter = new WooCommerceAdapter();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $item
     *
     * @return void
     */
    public function created(Model $itemGroupOptionItem)
    {
        if (false === $this->moduleIsEnabled('inventory')) {
            return;
        }

//        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
//            return;
//        }

        if (null !== request()->get('wc_variation_id')) {
            $integration = WooCommerceIntegration::firstOrNew(
                [
                    'company_id'     => company_id(),
                    'woocommerce_id' => request()->get('wc_variation_id'),
                    'item_type'      => Model::class,
                ],
                [
                    'item_id' => $itemGroupOptionItem->id,
                ]
            );

            if ($integration->exists) {
                $integration->item_id = $itemGroupOptionItem->id;
            }

            $integration->save();

            return;
        }

        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        // Item Group Variant Item
        if (false === request()->has('variant_value_id')) {
            return;
        }

        if (WooCommerceIntegration::where(
            [
                'item_id'    => $itemGroupOptionItem->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first()) {
            return;
        }

        $attributes = [];
        foreach (request()->get('variant_value_id') as $option_value_id) {
            $optionValue = VariantValue::find($option_value_id);

            if (null === $optionValue) {
                continue;
            }

            $optionIntegration = WooCommerceIntegration::where(
                [
                    'company_id' => company_id(),
                    'item_id'    => $optionValue->option->id,
                    'item_type'  => Variant::class,
                ]
            )->first();

            if (null === $optionIntegration) {
                continue;
            }

            $attributes[] = [
                'id'        => $optionIntegration->woocommerce_id,
                'option'    => $optionValue->name,
            ];
        }

        $itemIntegration = WooCommerceIntegration::where(
            [
                'company_id' => company_id(),
                'item_id'    => $itemGroupOptionItem->item_group_id,
                'item_type'  => ItemGroup::class,
            ]
        )->first();

        $productId = $this->adapter->storeProductVariation(
            $itemIntegration->woocommerce_id,
            [
                'regular_price' => (string) $itemGroupOptionItem->inventory_item->item->sale_price,
                'attributes'    => $attributes,
            ]
        );

        if (0 === $productId) {
            Log::error('WC Integration::: Product Variation is not synced:' . print_r($itemGroupOptionItem, true));
            return;
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $productId;
        $integration->item_id           = $itemGroupOptionItem->id;
        $integration->item_type         = Model::class;

        $integration->save();
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $itemGroupOptionItem
     *
     * @return void
     */
    public function deleted(Model $itemGroupOptionItem)
    {
        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(['item_id' => $itemGroupOptionItem->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
//            $this->adapter->deleteProduct($integration->woocommerce_id);
        }

        $integration->delete();
    }
}
