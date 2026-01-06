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
use Modules\Woocommerce\Jobs\SyncCategory;
use Psr\SimpleCache\InvalidArgumentException;

class Categories extends Controller
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

        $adapter = new WooCommerceAdapter();

        $categories = $adapter->getCategories(
            [
                'page'     => $page,
                'per_page' => $adapter->getPageLimit(),
            ]
        );

        foreach ($categories->data as $category) {
            $cachedCategories[$category->id] = $category;

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.categories', 1),
                        'value' => $category->name,
                    ]
                ),
                'url'  => route('woocommerce.category.store'),
                'page' => $page,
                'id'   => $category->id,
            ];
        }

        if (isset($cachedCategories)) {
            Cache::set(cache_prefix() . 'woocommerce_categories', $cachedCategories, Date::now()->addHours(6));
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
        $cachedCategories = Cache::get(cache_prefix() . 'woocommerce_categories');

        if (null === $cachedCategories) {
            $adapter = new WooCommerceAdapter();

            $categories = $adapter->getCategories(
                [
                    'page'     => $request['page'],
                    'per_page' => $adapter->getPageLimit(),
                ]
            );

            foreach ($categories->data as $category) {
                $cachedCategories[$category->id] = $category;
            }

            if (isset($cachedCategories)) {
                Cache::set(cache_prefix() . 'woocommerce_categories', $cachedCategories, Date::now()->addHours(6));
            }
        }

        $timestamp = Date::now()->toDateTimeString();

        try {
            $this->dispatchSync(new SyncCategory($cachedCategories[$request['id']]));
        } catch (\Exception $e) {
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
                'type' => trans_choice('woocommerce::general.types.categories', 2)
            ]);

            setting()->set('woocommerce.last_check', $timestamp);
            setting()->save();

            Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));
        }

        return response()->json($json);
    }
}
