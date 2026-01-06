<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Traits\Modules;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;

class Sync extends Controller
{
    use Modules;

    public function count()
    {
        $success = true;
        $error   = false;
        $message = null;
        $total   = 0;
        $pages   = [];

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => true,
                    'message' => trans('woocommerce::general.error.sync_already_running'),
                    'count'   => $total,
                    'pages'   => $pages,
                ]
            );
        }

        Cache::set(cache_prefix() . 'woocommerce_sync_running', true, Date::now()->addHours(6));

        $adapter = new WooCommerceAdapter();

        $this->getTaxes($adapter, $pages, $total);
        $this->getPaymentMethods($adapter, $pages, $total);
        $this->getCategories($adapter, $pages, $total);
        $this->getAttributes($adapter, $pages, $total);
        $this->getProducts($adapter, $pages, $total);
        $this->getCustomers($adapter, $pages, $total);
        $this->getOrders($adapter, $pages, $total);

        Cache::set(cache_prefix() . 'woocommerce_sync_total', $total, Date::now()->addHours(6));
        Cache::set(cache_prefix() . 'woocommerce_sync_count', 0, Date::now()->addHours(6));

        $message = trans('woocommerce::general.total', ['count' => $total]);

        if (empty($pages)) {
            Cache::forget(cache_prefix() . 'woocommerce_sync_running');

            $success = false;
            $error   = true;
            $message = trans('woocommerce::general.error.nothing_to_sync');
        }

        return response()->json(
            [
                'success' => $success,
                'error'   => $error,
                'message' => $message,
                'count'   => $total,
                'pages'   => $pages
            ]
        );
    }

    private function getTaxes(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        $taxes = $adapter->getTaxRates(
            [
                'page'     => 1,
                'per_page' => $adapter->getPageLimit(),
            ]

        );

        if (count($taxes->data) > 0) {
            $total += $taxes->meta["totalItem"];
            $totalPages = $taxes->meta["totalPage"];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'url' => route('woocommerce.tax.sync', $i),
                ];
            }
        }
    }

    private function getPaymentMethods(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        $paymentMethods = $adapter->getPaymentMethods();

        if (count($paymentMethods->data) > 0) {
            foreach ($paymentMethods->data as $paymentMethod) {
                $total++;
            }

            $pages[] = [
                'url' => route('woocommerce.payment-method.sync'),
            ];
        }
    }

    private function getCategories(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        $categories = $adapter->getCategories(['page' => 1, 'per_page' => $adapter->getPageLimit()]);

        if (count($categories->data) > 0) {
            $total += $categories->meta["totalItem"];
            $totalPages = $categories->meta["totalPage"];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'url' => route('woocommerce.category.sync', $i),
                ];
            }
        }
    }

    private function getAttributes(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        if (false === $this->moduleIsEnabled('inventory')) {
            return;
        }

        $attributes = $adapter->getProductAttributes();

        if (count($attributes->data) > 0) {
            foreach ($attributes->data as $attribute) {
                $total++;
            }

            $pages[] = [
                'url' => route('woocommerce.attribute.sync'),
            ];
        }
    }

    private function getProducts(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        $lastCheck = setting('woocommerce.last_check');

        $type = '';
        if (false === $this->moduleIsEnabled('inventory')) {
            $type = 'simple';
        }

        $products = $adapter->getProducts(
            array_merge(
                [
                    'page'     => 1,
                    'per_page' => $adapter->getPageLimit(),
                ],
                (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : [],
                (false === empty($type)) ? ['type' => $type] : []
            )
        );

        if (count($products->data) > 0) {
            $total += $products->meta["totalItem"];
            $totalPages = $products->meta["totalPage"];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'url' => route('woocommerce.product.sync', $i),
                ];
            }
        }
    }

    private function getCustomers(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        $lastCheck = setting('woocommerce.last_check');

        $customers = $adapter->getCustomers(
            array_merge(
                [
                    'page'     => 1,
                    'per_page' => $adapter->getPageLimit(),
                ],
                (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
            )
        );

        if (count($customers->data) > 0) {
            $total += $customers->meta["totalItem"];
            $totalPages = $customers->meta["totalPage"];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'url' => route('woocommerce.customer.sync', $i),
                ];
            }
        }
    }

    private function getOrders(WooCommerceAdapter $adapter, &$pages, &$total)
    {
        $lastCheck = setting('woocommerce.last_check');
        $orderStatusIds = setting('woocommerce.order_status_ids');

        $orders = $adapter->getOrders(
            array_merge(
                [
                    'page'     => 1,
                    'per_page' => $adapter->getPageLimit(),
                    'order'  => 'asc',
                ],
                (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : [],
                (null !== $orderStatusIds) ? ['status' => implode(',', json_decode($orderStatusIds))] : []
            )
        );

        if (count($orders->data) > 0) {
            $total += $orders->meta["totalItem"];
            $totalPages = $orders->meta["totalPage"];

            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = [
                    'url' => route('woocommerce.order.sync', $i),
                ];
            }
        }
    }
}
