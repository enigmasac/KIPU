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
use Modules\Woocommerce\Jobs\SyncPaymentMethod;
use Psr\SimpleCache\InvalidArgumentException;

class PaymentMethods extends Controller
{
    use Jobs;

    /**
     * Show the form for editing the specified resource.
     *
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function sync()
    {
        $steps = [];

        $adapter = new WooCommerceAdapter();

        $paymentMethods = $adapter->getPaymentMethods();

        foreach ($paymentMethods->data as $paymentMethod) {
            $cachedPaymentMethods[$paymentMethod->id] = $paymentMethod;

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.payment_methods', 1),
                        'value' => $paymentMethod->method_title,
                    ]
                ),
                'url'  => route('woocommerce.payment-method.store'),
                'page' => 1,
                'id'   => $paymentMethod->id,
            ];
        }

        if (isset($cachedPaymentMethods)) {
            Cache::set(cache_prefix() . 'woocommerce_payment_methods', $cachedPaymentMethods, Date::now()->addHours(6));
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
        $cachedPaymentMethods = Cache::get(cache_prefix() . 'woocommerce_payment_methods');

        if (null === $cachedPaymentMethods) {
            $paymentMethods = (new WooCommerceAdapter())->getPaymentMethods();

            foreach ($paymentMethods->data as $paymentMethod) {
                $cachedPaymentMethods[$paymentMethod->id] = $paymentMethod;
            }

            if (isset($cachedPaymentMethods)) {
                Cache::set(cache_prefix() . 'woocommerce_payment_methods', $cachedPaymentMethods, Date::now()->addHours(6));
            }
        }

        $timestamp = Date::now()->toDateTimeString();

        try {
            $this->dispatchSync(new SyncPaymentMethod($cachedPaymentMethods[$request['id']]));
        } catch (\Exception $e) {
            Log::error('WC Integration::: Exception:' . $e->getLine() .  ' - ' . $e->getCode() . ': ' . $e->getMessage());

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

        $syncCount = Cache::get(cache_prefix() . 'woocommerce_sync_count', 0) + 1;

        Cache::set(cache_prefix() . 'woocommerce_sync_count', $syncCount, Date::now()->addHours(6));

        $json = [
            'error' => false,
            'success' => true,
            'finished' => false,
            'message' => ''
        ];

        if ($syncCount === (int) Cache::get(cache_prefix() . 'woocommerce_sync_total', 0)) {
            $json['finished'] = true;
            $json['message'] = trans('woocommerce::general.finished', [
                'type' => trans_choice('woocommerce::general.types.payment_methods', 2)
            ]);

            setting()->set('woocommerce.last_check', $timestamp);
            setting()->save();

            Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));
        }

        return response()->json($json);
    }
}
