<?php

namespace Modules\Woocommerce\Observers\Income;

use App\Abstracts\Observer;
use App\Models\Document\Document as Model;
use Illuminate\Support\Facades\Cache;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Invoice extends Observer
{
    /**
     * Listen to the deleted event.
     *
     * @param Model $invoice
     *
     * @return void
     */
    public function deleted(Model $invoice)
    {
        if ($invoice->type !== Model::INVOICE_TYPE) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(['item_id' => $invoice->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        $integration->delete();
    }
}
