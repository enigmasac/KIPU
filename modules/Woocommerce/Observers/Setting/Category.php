<?php

namespace Modules\Woocommerce\Observers\Setting;

use App\Abstracts\Observer;
use App\Models\Setting\Category as Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Category extends Observer
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
     * @param Model $category
     *
     * @return void
     */
    public function created(Model $category)
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

        if (WooCommerceIntegration::where(['item_id' => $category->id, 'item_type' => Model::class, 'company_id' => company_id()])->first()) {
            return;
        }

        if ('item' !== $category->type) {
            return;
        }

        $params = [
            'name'  => $category->name,
        ];

        $categoryId = $this->adapter->storeCategory($params);

        if (0 === $categoryId) {
            Log::error('WC Integration::: Category is not synced:' . print_r($category, true));
            return;
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $categoryId;
        $integration->item_id           = $category->id;
        $integration->item_type         = Model::class;

        $integration->save();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $category
     *
     * @return void
     */
    public function updated(Model $category)
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

        $integration = WooCommerceIntegration::where(['item_id' => $category->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        if ('item' !== $category->type) {
            return;
        }

        $params = [
            'name'  => $category->name,
        ];

        $this->adapter->updateCategory(
            $integration->woocommerce_id,
            $params
        );


        $integration->save();
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $category
     *
     * @return void
     */
    public function deleted(Model $category)
    {
        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(['item_id' => $category->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        if ('item' !== $category->type) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
            $this->adapter->deleteCategory($integration->woocommerce_id);
        }

        $integration->delete();
    }
}
