<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Traits\Jobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Jobs\SyncContact;
use Psr\SimpleCache\InvalidArgumentException;

class Customers extends Controller
{
    use Jobs;

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $page
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function sync($page)
    {
        $steps = [];
        $lastCheck = setting('woocommerce.last_check');

        Cache::forget(cache_prefix() . 'woocommerce_customers');

        $adapter = new WooCommerceAdapter();

        $customers = $adapter->getCustomers(
            array_merge(
                [
                    'page'     => $page,
                    'per_page' => $adapter->getPageLimit(),
                ],
                (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
            )
        );

        foreach ($customers->data as $customer) {
            $cachedCustomers[$customer->id] = $customer;

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.contacts', 1),
                        'value' => $customer->first_name . ' ' . $customer->last_name,
                    ]
                ),
                'url'  => route('woocommerce.customer.store'),
                'page' => $page,
                'id'   => $customer->id,
            ];
        }

        if (isset($cachedCustomers)) {
            Cache::set(cache_prefix() . 'woocommerce_customers', $cachedCustomers, Date::now()->addHours(6));
        }

        return response()->json(
            [
                'error'  => false,
                'success' => true,
                'steps'   => $steps,
            ]
        );
    }

    /**
     * Enable the specified resource.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function store(Request $request)
    {
        $cachedCustomers = Cache::get(cache_prefix() . 'woocommerce_customers');

        if (null === $cachedCustomers) {
            $adapter = new WooCommerceAdapter();
            $lastCheck = setting('woocommerce.last_check');

            $customers = $adapter->getCustomers(
                array_merge(
                    [
                        'page'     => $request['page'],
                        'per_page' => $adapter->getPageLimit(),
                    ],
                    (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                )
            );

            foreach ($customers->data as $customer) {
                $cachedCustomers[$customer->id] = $customer;
            }

            if (isset($cachedCustomers)) {
                Cache::set(cache_prefix() . 'woocommerce_customers', $cachedCustomers, Date::now()->addHours(6));
            }
        }

        $timestamp = Date::now()->toDateTimeString();

        try {
            $this->dispatchSync(new SyncContact($cachedCustomers[$request['id']]));
        } catch (\Exception $e) {
            Log::error(
                'WC Integration::: Exception:' . basename($e->getFile()) . ':' . $e->getLine() . ' - '
                . $e->getCode() . ': ' . $e->getMessage()
            );

            report($e);

            Cache::forget(cache_prefix() . 'woocommerce_sync_running');

            return response()->json([
                'error'   => true,
                'success'  => false,
                'finished' => false,
                'message'  => $e->getMessage()
            ]);
        }

        $syncCount = Cache::get(cache_prefix() . 'woocommerce_sync_count', 0) + 1;

        Cache::set(cache_prefix() . 'woocommerce_sync_count', $syncCount, Date::now()->addHours(6));

        $json = [
            'error'   => false,
            'success'  => true,
            'finished' => false,
            'message'  => ''
        ];

        if ($syncCount === (int) Cache::get(cache_prefix() . 'woocommerce_sync_total', 0)) {
            $json['finished'] = true;
            $json['message']  = trans(
                'woocommerce::general.finished',
                [
                    'type' => trans_choice('woocommerce::general.types.contacts', 2),
                ]
            );

            setting()->set('woocommerce.last_check', $timestamp);
            setting()->save();

            Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));
        }

        return response()->json($json);
    }
}
