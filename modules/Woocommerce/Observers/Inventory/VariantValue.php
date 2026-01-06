<?php

namespace Modules\Woocommerce\Observers\Inventory;

use App\Abstracts\Observer;
use App\Traits\Modules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Models\VariantValue as Model;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class VariantValue extends Observer
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
    public function created(Model $optionValue)
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

        if (WooCommerceIntegration::where(
            [
                'item_id'    => $optionValue->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first()) {
            return;
        }

        $optionIntegration = WooCommerceIntegration::where(
            [
                'item_id'    => $optionValue->variant_id,
                'item_type'  => Variant::class,
                'company_id' => company_id(),
            ]
        )->first();

        $attributeTermId = $this->adapter->storeAttributeTerm(
            $optionIntegration->woocommerce_id,
            ['name' => $optionValue->name]
        );

        if (0 === $attributeTermId) {
            Log::error('WC Integration::: Attribute Term is not synced:' . print_r($optionValue, true));
            return;
        }

        $integration                 = new WooCommerceIntegration();
        $integration->company_id     = company_id();
        $integration->woocommerce_id = $attributeTermId;
        $integration->item_id        = $optionValue->id;
        $integration->item_type      = Model::class;

        $integration->save();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $optionValue
     *
     * @return void
     */
    public function updated(Model $optionValue)
    {
        if (false === (bool)setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(
            [
                'item_id'    => $optionValue->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first();

        if (null === $integration) {
            return;
        }

        $optionIntegration = WooCommerceIntegration::where(
            [
                'item_id'    => $optionValue->variant_id,
                'item_type'  => Variant::class,
                'company_id' => company_id(),
            ]
        )->first();

        $this->adapter->updateAttributeTerm(
            $optionIntegration->woocommerce_id,
            $integration->woocommerce_id,
            ['name' => $optionValue->name]
        );
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $optionValue
     *
     * @return void
     */
    public function deleted(Model $optionValue)
    {
        $integration = WooCommerceIntegration::where(
            [
                'item_id'    => $optionValue->id,
                'item_type'  => Model::class,
                'company_id' => company_id(),
            ]
        )->first();

        if (null === $integration) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
            $optionIntegration = WooCommerceIntegration::where(
                [
                    'item_id'    => $optionValue->variant_id,
                    'item_type'  => Variant::class,
                    'company_id' => company_id(),
                ]
            )->first();

            $this->adapter->deleteAttributeTerm(
                $optionIntegration->woocommerce_id,
                $integration->woocommerce_id,
                ['force' => true]
            );
        }

        $integration->delete();
    }
}
