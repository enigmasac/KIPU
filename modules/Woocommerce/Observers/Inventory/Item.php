<?php

namespace Modules\Woocommerce\Observers\Inventory;

use App\Abstracts\Observer;
use App\Models\Common\Item as ItemModel;
use App\Models\Setting\Category;
use App\Traits\Modules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Item as Model;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Item extends Observer
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
    public function created(Model $inventory_item)
    {
        if (false === $this->moduleIsEnabled('inventory')) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        // Item Group Variant Item
        if (request()->has('variant_value_id')) {
            return;
        }

        if (WooCommerceIntegration::where(['item_id' => $inventory_item->item_id, 'item_type' => Model::class, 'company_id' => company_id()])->first()) {
            return;
        }

        $item = ItemModel::where('id', $inventory_item->item_id)->first();

        $categoryIntegration = WooCommerceIntegration::where(
            [
                'company_id' => company_id(),
                'item_id'   => $item->category_id,
                'item_type' => Category::class,
            ]
        )->first();

        if (empty($categoryIntegration) && false === empty($item->category_id)) {
            $category = Category::where('id', $item->category_id)->first();

            $woocommerce_category_id = $this->adapter->storeCategory(
                [
                    'name'  => $category->name,
                ]
            );

            $categoryIntegration                    = new WooCommerceIntegration();
            $categoryIntegration->company_id        = company_id();
            $categoryIntegration->woocommerce_id    = $woocommerce_category_id;
            $categoryIntegration->item_id           = $category->id;
            $categoryIntegration->item_type         = Category::class;

            $categoryIntegration->save();
        }

        $status = $item->enabled == true ? 'publish' : 'private';

        $categories = [];
        if (null !== $categoryIntegration && null !== $categoryIntegration->woocommerce_id) {
            $categories = ['categories' => [['id' => $categoryIntegration->woocommerce_id]]];
        }

        $params = [
            'name'          => $item->name,
            'type'          => 'simple',
            'regular_price' => (string) $item->sale_price,
            'description'   => $item->description ?? '',
            'status'        => $status
        ];

        $params = array_merge($params, $categories);

        //Inventory Stock
        $params['stock_quantity'] = $inventory_item->opening_stock;
        $params['sku']      = $inventory_item->sku;

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
    public function updated(Model $inventory_item)
    {
        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $item = ItemModel::where('id', $inventory_item->item_id)->first();

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
                'type'          => 'simple',
                'regular_price' => (string)$item->sale_price,
                'description'   => $item->description ?? '',
                'status'        => $status,
            ]
        );

        //Inventory Stock
        $params['stock_quantity'] = $inventory_item->opening_stock;
        $params['sku']      = $inventory_item->sku;

        $this->adapter->updateProduct($integration->woocommerce_id, $params);
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
