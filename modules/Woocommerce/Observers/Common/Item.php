<?php

namespace Modules\Woocommerce\Observers\Common;

use App\Abstracts\Observer;
use App\Models\Common\Item as Model;
use App\Models\Setting\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Item extends Observer
{
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
    public function created(Model $item)
    {
        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        // Item Group Variant Item
        if (request()->has('variant_value_id') || request()->has('track_inventory')) {
            return;
        }

        if (empty(setting('woocommerce.consumer_secret', '')) || empty(setting('woocommerce.consumer_key', ''))) {
            flash(trans('woocommerce::general.form.not_transferred'));
            return;
        }

        if (WooCommerceIntegration::where(['item_id' => $item->id, 'item_type' => Model::class, 'company_id' => company_id()])->first()) {
            return;
        }

        $categoryIntegration = WooCommerceIntegration::where(
            [
                'company_id' => company_id(),
                'item_id'   => $item->category_id,
                'item_type' => Category::class,
            ]
        )->first();

        $status = $item->enabled == true ? 'publish' : 'private';

        $categories = [];
        if (null !== $categoryIntegration && null !== $categoryIntegration->woocommerce_id) {
            $categories = ['categories' => [['id' => $categoryIntegration->woocommerce_id]]];
        }

        $params = [
            'name'          => $item->name,
            'regular_price' => (string) $item->sale_price,
            'description'   => $item->description ?? '',
            'status'        => $status
        ];

        $params = array_merge($params, $categories);

        $productId = $this->adapter->storeProduct($params);

        if (0 === $productId) {
            Log::error('WC Integration::: Product is not synced:' . print_r($item, true));
            return;
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $productId;
        $integration->item_id           = $item->id;
        $integration->item_type         = Model::class;

        $integration->save();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $item
     *
     * @return void
     */
    public function updated(Model $item)
    {
        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        if (empty(setting('woocommerce.consumer_secret', '')) || empty(setting('woocommerce.consumer_key', ''))) {
            flash(trans('woocommerce::general.form.not_transferred'));
        }

        $integration = WooCommerceIntegration::where(['item_id' => $item->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        $categoryIntegration = WooCommerceIntegration::where(
            [
                'company_id' => company_id(),
                'item_id'   => $item->category_id,
                'item_type' => Category::class,
            ]
        )->first();

        $status = $item->enabled == true ? 'publish' : 'private';

        $params = array_merge(
            null !== $categoryIntegration ? ['categories' => [['id' => $categoryIntegration->woocommerce_id]]] : [],
            [
                'name'          => $item->name,
                'regular_price' => (string)$item->sale_price,
                'description'   => $item->description ?? '',
                'status'        => $status,
            ]
        );

        $this->adapter->updateProduct($integration->woocommerce_id, $params);


        $integration->save();
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $item
     *
     * @return void
     */
    public function deleted(Model $item)
    {
        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(['item_id' => $item->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
            $this->adapter->deleteProduct($integration->woocommerce_id);
        }

        $integration->delete();
    }
}
