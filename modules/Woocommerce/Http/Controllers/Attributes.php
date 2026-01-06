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
use Modules\Woocommerce\Jobs\SyncAttribute;
use Modules\Woocommerce\Jobs\SyncAttributeTerm;
use Psr\SimpleCache\InvalidArgumentException;

class Attributes extends Controller
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

        $attributes = $adapter->getProductAttributes();

        $cachedAttributes = [];
        foreach ($attributes->data as $attribute) {
            $cachedAttributes[$attribute->id] = $this->getAttributeTerms($adapter, $attribute);

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.attributes', 1),
                        'value' => $attribute->name,
                    ]
                ),
                'url'  => route('woocommerce.attribute.store'),
                'page' => 1,
                'id'   => $attribute->id,
            ];
        }

        if (0 < count($cachedAttributes)) {
            Cache::set(cache_prefix() . 'woocommerce_attributes', $cachedAttributes, Date::now()->addHours(6));
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
        $cachedAttributes = Cache::get(cache_prefix() . 'woocommerce_attributes');

        if (null === $cachedAttributes) {
            $adapter = new WooCommerceAdapter();
            $attributes = $adapter->getProductAttributes();

            $cachedAttributes = [];
            foreach ($attributes->data as $attribute) {
                $cachedAttributes[$attribute->id] = $this->getAttributeTerms($adapter, $attribute);
            }

            if (0 < count($cachedAttributes)) {
                Cache::set(cache_prefix() . 'woocommerce_attributes', $cachedAttributes, Date::now()->addHours(6));
            }
        }

        $timestamp = Date::now()->toDateTimeString();

        try {
            $this->dispatchSync(new SyncAttribute($cachedAttributes[$request['id']]));

            foreach ($cachedAttributes[$request['id']]->terms as $term) {
                $term->attribute_id = $request['id'];

                $this->dispatchSync(new SyncAttributeTerm($term));
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
                'type' => trans_choice('woocommerce::general.types.attributes', 2)
            ]);

            setting()->set('woocommerce.last_check', $timestamp);
            setting()->save();

            Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));
        }

        return response()->json($json);
    }

    private function getAttributeTerms(WooCommerceAdapter $adapter, $attribute)
    {
        $terms = $adapter->getAttributeTerms($attribute->id, ['page' => 1, 'per_page' => $adapter->getPageLimit()]);

        $attribute->terms = [];

        if (count($terms->data) > 0) {
            $totalPages = $terms->meta['totalPage'];

            for ($i = 2; $i <= $totalPages; $i++) {
                $result =
                    $adapter->getAttributeTerms($attribute->id, ['page' => $i, 'per_page' => $adapter->getPageLimit()]);
                if (count($result->data) <= 0) {
                    continue;
                }

                foreach ($result->data as $datum) {
                    $terms->data[] = $datum;
                }
            }

            $attribute->terms = $terms->data;
        }

        return $attribute;
    }
}
