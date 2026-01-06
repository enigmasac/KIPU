<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Traits\Jobs;
use App\Traits\Modules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Jobs\SyncProduct;
use Modules\Woocommerce\Jobs\SyncVariableProduct;
use Psr\SimpleCache\InvalidArgumentException;

class Products extends Controller
{
    use Jobs;
    use Modules;

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

        $type = '';
        if (false === $this->moduleIsEnabled('inventory')) {
            $type = 'simple';
        }

        $adapter = new WooCommerceAdapter();

        $products = $adapter->getProducts(
            array_merge(
                [
                    'page'     => $page,
                    'per_page' => $adapter->getPageLimit(),
                ],
                (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : [],
                (false === empty($type)) ? ['type' => $type] : []
            )
        );

        foreach ($products->data as $product) {
            if ('variable' === $product->type) {
                if (false === $this->moduleIsEnabled('inventory')) {
                    Log::info(
                        "WC Integration::: Product ID: $product->id " .
                        "Variable Product ($product->name) is detected. " .
                        "Please use Inventory App to sync variable products."
                    );
                    continue;
                }

//                if (empty($product->variations)) {
//                    continue;
//                }

                $product->akaunting_items = $adapter->getProductVariations(
                    $product->id,
                    array_merge(
                        ['page'  => 1, 'per_page' => 100],
//                        (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                    )
                )->data;
            }

            $cachedProducts[$product->id] = $product;

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.items', 1),
                        'value' => $product->name,
                    ]
                ),
                'url'  => route('woocommerce.product.store'),
                'page' => $page,
                'id'   => $product->id,
            ];
        }

        if (isset($cachedProducts)) {
            Cache::set(cache_prefix() . 'woocommerce_products', $cachedProducts, Date::now()->addHours(6));
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
        $cachedProducts = Cache::get(cache_prefix() . 'woocommerce_products');

        if (null === $cachedProducts) {
            $type = '';
            if (false === $this->moduleIsEnabled('inventory')) {
                $type = 'simple';
            }

            $adapter = new WooCommerceAdapter();
            $lastCheck = setting('woocommerce.last_check');

            $products = $adapter->getProducts(
                array_merge(
                    [
                        'page'     => $request['page'],
                        'per_page' => $adapter->getPageLimit(),
                    ],
                    (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : [],
                    (false === empty($type)) ? ['type' => $type] : []
                )
            );

            foreach ($products->data as $product) {
                if ('variable' === $product->type) {
                    if (false === $this->moduleIsEnabled('inventory')) {
                        Log::info(
                            "WC Integration::: Product ID: $product->id " .
                            "Variable Product ($product->name) is detected. " .
                            "Please use Inventory App to sync variable products."
                        );
                        continue;
                    }

                    if (empty($product->variations)) {
                        continue;
                    }

                    $product->akaunting_items = $adapter->getProductVariations(
                        $product->id,
                        array_merge(
                            ['page'  => 1, 'per_page' => 100],
                            (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                        )
                    )->data;
                }

                $cachedProducts[$product->id] = $product;
            }

            if (isset($cachedProducts)) {
                Cache::set(cache_prefix() . 'woocommerce_products', $cachedProducts, Date::now()->addHours(6));
            }
        }

        $timestamp = Date::now()->toDateTimeString();

        if (isset($cachedProducts[$request['id']]->akaunting_items) && empty($cachedProducts[$request['id']]->akaunting_items)) {
            Cache::increment(cache_prefix() . 'woocommerce_sync_count');

            return response()->json([
                'error' => true,
                'success' => true,
                'finished' => false,
                'message' => trans('woocommerce::general.error.product_no_variation', ['id' => $request['id']])
            ]);
        }


        try {
            if (isset($cachedProducts[$request['id']]->akaunting_items)) {
                $this->dispatchSync(new SyncVariableProduct($cachedProducts[$request['id']]));
            } else {
                $this->dispatchSync(new SyncProduct($cachedProducts[$request['id']]));
            }
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
                'type' => trans_choice('woocommerce::general.types.items', 2)
            ]);

            setting()->set('woocommerce.last_check', $timestamp);
            setting()->save();

            Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));
        }

        return response()->json($json);
    }
}
