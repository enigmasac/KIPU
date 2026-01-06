<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Models\Banking\Account;
use App\Models\Setting\Category;
use App\Traits\Modules;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\CustomFields\Models\Field;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Http\Requests\Setting as Request;

class Settings extends Controller
{
    use Modules;

    /**
     * Show the form for editing the specified resource.
     *
     * @return Factory|View
     */
    public function edit()
    {
        if ('custom' === request()->get('type', 'auto')) {
            setting()->set('woocommerce.url', null);
            setting()->set('woocommerce.consumer_key', null);
            setting()->set('woocommerce.consumer_secret', null);
            setting()->save();
        }

        $accounts = Account::enabled()->pluck('name', 'id');

        $invoiceCategories = Category::enabled()->type('income')->pluck('name', 'id');

        $apiConnectionOk = $this->isAPIConnectionOk();

        $customFieldsMessage = '';
        $customFieldsInstalled = true;
        if (false === $this->moduleIsEnabled('custom-fields')) {
            $customFieldsInstalled = false;
            $customFieldsMessage = trans(
                'woocommerce::general.form.install_custom_fields',
                ['link' => '#']
            );
        }

        $wpFields = collect([]);
        if ($apiConnectionOk) {
            $adapter = new WooCommerceAdapter();
            $wpFields = collect($adapter->getCustomFields()->data);
        }

        $fields = collect(['document_number' => trans('invoices.invoice_number')]);

        if ($customFieldsInstalled) {
            $customFields = Field::enabled()->orderBy('name')->pluck('name', 'id');
        }

        if (isset($customFields)) {
            $fields = $customFields->union($fields);
        }

        $orderStatuses = [
            'pending'       => trans('woocommerce::general.status.pending'),
            'processing'    => trans('woocommerce::general.status.processing'),
            'on-hold'       => trans('woocommerce::general.status.on_hold'),
            'completed'     => trans('woocommerce::general.status.completed'),
            'cancelled'     => trans('woocommerce::general.status.cancelled'),
            'refunded'      => trans('woocommerce::general.status.refunded'),
            'failed'        => trans('woocommerce::general.status.failed'),
            'trash'         => trans('woocommerce::general.status.trash')
        ];

        $runningBackgroundMessage = Cache::get(cache_prefix() . 'woocommerce_sync_running', false)
                                    ? trans('woocommerce::general.error.restart', ['route' => route('woocommerce.restart')])
                                    : null;

        return view(
            'woocommerce::edit',
            compact(
                'invoiceCategories',
                'accounts',
                'orderStatuses',
                'apiConnectionOk',
                'customFieldsMessage',
                'runningBackgroundMessage',
                'customFieldsInstalled',
                'wpFields',
                'fields'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function update(Request $request)
    {
        setting()->set('woocommerce.url', $request['url']);
        setting()->set('woocommerce.consumer_key', $request['consumer_key']);
        setting()->set('woocommerce.consumer_secret', $request['consumer_secret']);
        setting()->set('woocommerce.invoice_category_id', $request['invoice_category_id']);

        if ((null !== $request['order_status_ids'])) {
            setting()->set('woocommerce.order_status_ids', json_encode($request['order_status_ids']));
        } else {
            setting()->set('woocommerce.order_status_ids', null);
        }

        setting()->set('woocommerce.two_way_create_update', $request['two_way_create_update']);
        setting()->set('woocommerce.two_way_delete', $request['two_way_delete']);

        if (null !== $request['items']) {
            setting()->set('woocommerce.field_mapping', json_encode($request['items']));
        } else {
            setting()->forget('woocommerce.field_mapping');
        }

        setting()->save();

        $response = [
            'status'   => null,
            'success'  => true,
            'error'    => false,
            'message'  => trans('messages.success.updated', ['type' => trans('woocommerce::general.name')]),
            'data'     => null,
            'redirect' => route('woocommerce.edit'),
        ];

        session(['aka_notify' => $response]);

        return response()->json($response);
    }

    public function restart()
    {
        Cache::forget(cache_prefix() . 'woocommerce_sync_running');

        return redirect(url()->previous());
    }

    /**
     * @return bool
     */
    private function isAPIConnectionOk()
    {
        if (
            !setting('woocommerce.url')
            || !setting('woocommerce.consumer_key')
            || !setting('woocommerce.consumer_secret')
        ) {
            return true;
        }

        try {
            try {
                $client = $this->checkConnection();
            } catch (HttpClientException $e) {
                $client = $this->checkConnection(true);
            }
        } catch (HttpClientException $e) {
            Log::error(
                'WC Integration::: Exception:' . $e->getLine() . ' - ' . $e->getCode() . ': ' . $e->getMessage()
            );

            return false;
        }

        return 200 === $client->http->getResponse()->getCode();
    }

    /**
     * @param false $queryStringAuth
     *
     * @return Client
     * @throws HttpClientException
     */
    private function checkConnection($queryStringAuth = false): Client
    {
        try {
            $woocommerce = new WooCommerceAdapter();
            $client      = $woocommerce->login($queryStringAuth);
            $client->get('data');
        } catch (HttpClientException $e) {
            throw $e;
        }

        return $client;
    }
}
