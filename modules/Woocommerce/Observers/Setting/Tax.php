<?php

namespace Modules\Woocommerce\Observers\Setting;

use App\Abstracts\Observer;
use App\Models\Setting\Tax as Model;
use Illuminate\Support\Facades\Cache;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Tax extends Observer
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
     * @param Model $tax
     *
     * @return void
     */
    public function updated(Model $tax)
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

        $integration = WooCommerceIntegration::where(['item_id' => $tax->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        $this->adapter->updateTaxRates(
            $integration->woocommerce_id,
            [
                'name'  => $tax->name,
                'rate'  => $tax->rate
            ]
        );
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $tax
     *
     * @return void
     */
    public function deleted(Model $tax)
    {
        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(['item_id' => $tax->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
            $this->adapter->deleteTaxRates($integration->woocommerce_id);
        }

        $integration->delete();
    }
}
