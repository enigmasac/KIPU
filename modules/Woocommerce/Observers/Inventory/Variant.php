<?php

namespace Modules\Woocommerce\Observers\Inventory;

use App\Abstracts\Observer;
use App\Traits\Modules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Variant as Model;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Variant extends Observer
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
    public function created(Model $option)
    {
        if (false === $this->moduleIsEnabled('inventory')) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        if (false === (bool)setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (false === in_array($option->type, ['select', 'radio', 'checkbox'])) {
            return;
        }

        if (WooCommerceIntegration::where(
            [
                'item_id'    => $option->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first()) {
            return;
        }

        $attributeId = $this->adapter->storeProductAttribute(['name' => $option->name]);

        if (0 === $attributeId) {
            Log::error('WC Integration::: Attribute is not synced:' . print_r($option, true));
            return;
        }

        $integration                 = new WooCommerceIntegration();
        $integration->company_id     = company_id();
        $integration->woocommerce_id = $attributeId;
        $integration->item_id        = $option->id;
        $integration->item_type      = Model::class;

        $integration->save();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $option
     *
     * @return void
     */
    public function updated(Model $option)
    {
        if (false === (bool)setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        if (false === in_array($option->type, ['select', 'radio', 'checkbox'])) {
            return;
        }

        $integration = WooCommerceIntegration::where(
            [
                'item_id'    => $option->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first();

        if (null === $integration) {
            return;
        }

        $this->adapter->updateProductAttribute($integration->woocommerce_id, ['name' => $option->name]);
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $option
     *
     * @return void
     */
    public function deleted(Model $option)
    {
        $integration = WooCommerceIntegration::where(
            [
                'item_id'    => $option->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first();

        if (null === $integration) {
            return;
        }

        if (false === in_array($option->type, ['select', 'radio', 'checkbox'])) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
            $this->adapter->deleteProductAttribute($integration->woocommerce_id);
        }

        $integration->delete();
    }
}
