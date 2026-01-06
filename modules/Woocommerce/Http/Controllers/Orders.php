<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Traits\Jobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Jobs\SyncOrder;
use Psr\SimpleCache\InvalidArgumentException;

class Orders extends Controller
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
        $orderStatusIds = setting('woocommerce.order_status_ids');

        $adapter = new WooCommerceAdapter();

        $orders = $adapter->getOrders(
            array_merge(
                [
                    'page'     => $page,
                    'per_page' => $adapter->getPageLimit(),
                    'order'  => 'asc',
                ],
                (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : [],
                (null !== $orderStatusIds) ? ['status' => implode(',', json_decode($orderStatusIds))] : []
            )
        );

        foreach ($orders->data as $order) {
            $cachedOrders[$order->id] = $order;

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.orders', 1),
                        'value' => $order->number,
                    ]
                ),
                'url'  => route('woocommerce.order.store'),
                'page' => $page,
                'id'   => $order->id,
            ];
        }

        if (isset($cachedOrders)) {
            Cache::set(cache_prefix() . 'woocommerce_orders', $cachedOrders, Date::now()->addHours(6));
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
        $cachedOrders = Cache::get(cache_prefix() . 'woocommerce_orders');

        if (null === $cachedOrders) {
            $adapter = new WooCommerceAdapter();
            $lastCheck = setting('woocommerce.last_check');

            $orders = $adapter->getOrders(
                array_merge(
                    [
                        'page'     => $request['page'],
                        'per_page' => $adapter->getPageLimit(),
                        'order'  => 'asc',
                    ],
                    (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                )
            );

            foreach ($orders->data as $order) {
                $cachedOrders[$order->id] = $order;
            }

            if (isset($cachedOrders)) {
                Cache::set(cache_prefix() . 'woocommerce_orders', $cachedOrders, Date::now()->addHours(6));
            }
        }

        $timestamp = Date::now()->toDateTimeString();

        if (empty($cachedOrders[$request['id']]->line_items)) {
            $json = [
                'error'    => true,
                'success'  => true,
                'finished' => false,
                'message'  => trans('woocommerce::general.error.order_no_item', ['id' => $request['id']]),
            ];

            if ($this->isFinished($timestamp)) {
                $json['error'] = false;
                $json['finished'] = true;
                $json['message']  = trans(
                    'woocommerce::general.finished',
                    ['type' => trans_choice('woocommerce::general.types.invoices', 2)]
                );
            }

            return response()->json($json);
        }

        try {
            $this->dispatchSync(new SyncOrder($cachedOrders[$request['id']]));
        } catch (\Exception $e) {
            Log::error(
                'WC Integration::: Exception:' . basename($e->getFile()) . ':' . $e->getLine() . ' - '
                . $e->getCode() . ': ' . $e->getMessage()
            );

            report($e);

            Cache::forget(cache_prefix() . 'woocommerce_sync_running');

            return response()->json(
                [
                    'error'    => true,
                    'success'  => false,
                    'finished' => false,
                    'message'  => $e->getMessage(),
                ]
            );
        }

        $json = [
            'error'   => false,
            'success'  => true,
            'finished' => false,
            'message'  => ''
        ];

        if ($this->isFinished($timestamp)) {
            $json['finished'] = true;
            $json['message']  = trans(
                'woocommerce::general.finished',
                ['type' => trans_choice('woocommerce::general.types.invoices', 2)]
            );
        }

        return response()->json($json);
    }

    private function isFinished(string $timestamp): bool
    {
        $syncCount = Cache::get(cache_prefix() . 'woocommerce_sync_count', 0) + 1;

        Cache::set(cache_prefix() . 'woocommerce_sync_count', $syncCount, Date::now()->addHours(6));

        if ($syncCount !== (int) Cache::get(cache_prefix() . 'woocommerce_sync_total', 0)) {
            return false;
        }

        setting()->set('woocommerce.last_check', $timestamp);
        setting()->save();

        Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));

        return true;
}
}
