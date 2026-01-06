<?php

namespace Modules\Woocommerce\Adapters;

use Automattic\WooCommerce\Client as WooCommerceClient;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use Automattic\WooCommerce\HttpClient\Options;
use Automattic\WooCommerce\HttpClient\Response;
use Illuminate\Support\Facades\Log;

class WooCommerceAdapter extends Adapter implements AdapterInterface
{
    private $pageLimit = 100;

    public const AUTH_ENDPOINT = '/wc-auth/v1/authorize';

    public function login($queryStringAuth = false, $version = Options::VERSION)
    {
        return new WooCommerceClient(
            'https://' . setting('woocommerce.url'), // Your store URL
            setting('woocommerce.consumer_key'), // Your store key
            setting('woocommerce.consumer_secret'), // Your store secret
            [
                'query_string_auth' => $queryStringAuth,
//                 'verify_ssl' => false,
                'version' => $version
            ]
        );
    }

    public function remote($action, $options, $method, $version = Options::VERSION)
    {
        if (setting('woocommerce.consumer_key') == null) {
            $response = new \stdClass();
            $response->error = true;
            return $response;
        }

        $woocommerce = $this->login(false, $version);

        Log::debug('WC Integration::: Endpoint: ' . $action . ' Post Data: ' . print_r($options, true));

        $response = new \stdClass();
        $lastResponse = new Response();

        try {
            try {
                $response->data = $woocommerce->$method($action, $options);
                $lastResponse   = $woocommerce->http->getResponse();

                if (401 === $lastResponse->getCode()) {
                    $woocommerce = $this->login(true, $version);

                    $response->data = $woocommerce->$method($action, $options);
                    $lastResponse   = $woocommerce->http->getResponse();
                }

                $this->getResponseMetaData($lastResponse, $method, $action, $response);
            } catch (HttpClientException  $e) {
                if (417 === $e->getCode()) {
                    throw new \RuntimeException($e->getMessage(), $e->getCode());
                }

                if (401 === $e->getCode()) {
                    $woocommerce = $this->login(true, $version);

                    $response->data = $woocommerce->$method($action, $options);
                    $lastResponse   = $woocommerce->http->getResponse();
                }

                if (200 === $lastResponse->getCode()) {
                    $this->getResponseMetaData($lastResponse, $method, $action, $response);

                    return $response;
                }

                Log::error(
                    'WC Integration::: Exception:' . $e->getLine() . ' - ' . $e->getCode() . ': ' . $e->getMessage()
                );

                $response        = new \stdClass();
                $response->error = true;
                return $response;
            }
        } catch (HttpClientException $e) {
            Log::error(
                'WC Integration::: Exception:' . $e->getLine() . ' - ' . $e->getCode() . ': ' . $e->getMessage()
            );

            if (401 === $e->getCode() || 417 === $e->getCode()) {
                throw new \RuntimeException($e->getMessage(), $e->getCode());
            }

            $response        = new \stdClass();
            $response->error = true;
            return $response;
        }

        return $response;
    }

    public function createRemote($action, $data = [], $version = Options::VERSION)
    {
        return $this->remote($action, $data, 'post', $version);
    }

    public function deleteRemote($action, $data = [], $version = Options::VERSION)
    {
        return $this->remote($action, $data, 'delete', $version);
    }

    public function updateRemote($action, $data = [], $version = Options::VERSION)
    {
        return $this->remote($action, $data, 'put', $version);
    }

    public function getRemote($action, $data = [], $version = Options::VERSION)
    {
        return $this->remote($action, $data, 'get', $version);
    }

    // Taxes
    public function getTaxRates($options = [])
    {
        $response = $this->getRemote('taxes', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }
        return $response;
    }

    // Taxes
    public function getPaymentMethods($options = [])
    {
        $response = $this->getRemote('payment_gateways', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }
        return $response;
    }

    public function storeTaxRates($data)
    {
        $response = $this->createRemote('taxes', $data);

        if (null !== $response && isset($response->error)) {
            return 0;
        }

        return $response->id;
    }

    public function updateTaxRates($tax_id, $data): bool
    {
        $response = $this->updateRemote('taxes/' . $tax_id, $data);

        return !(null !== $response && isset($response->success));
    }

    public function deleteTaxRates($tax_id, $options = []): bool
    {
        $response = $this->deleteRemote('taxes/' . $tax_id, $options);

        return !($response != false);
    }

    public function getCategories($options = [])
    {
        $response = $this->getRemote('products/categories', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function storeCategory($options): int
    {
        $response = $this->createRemote('products/categories', $options);

        if (null !== $response && isset($response->error)) {
            $response = $this->getCategories(['search' =>  $options['name']]);

            if (null !== $response && isset($response->error)) {
                return 0;
            }

            return $response->data[0]->id;
        }

        return $response->data->id;
    }

    public function updateCategory($category_id, $data): bool
    {
        $response = $this->updateRemote('products/categories/' . $category_id, $data);

        return !(null !== $response && isset($response->success));
    }

    public function deleteCategory($category_id, $options = []): bool
    {
        $response = $this->deleteRemote('products/categories/' . $category_id, $options);

        return !(null !== $response && isset($response->success));
    }

    public function getProducts($options = [])
    {
        $response = $this->getRemote('products', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function storeProduct($data): int
    {
        $response = $this->createRemote('products', $data);

        if (null !== $response && isset($response->error)) {
            return 0;
        }

        return $response->data->id;
    }

    public function updateProduct($product_id, $options): bool
    {
        $response = $this->updateRemote('products/' . $product_id, $options);

        return !(null !== $response && isset($response->success));
    }

    public function deleteProduct($product_id, $options = []): bool
    {
        $response = $this->deleteRemote('products/' . $product_id, $options);

        return !(null !== $response && isset($response->success));
    }

    public function getCustomers($options = [])
    {
        $response = $this->getRemote('customers', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function storeCustomer($options)
    {
        $response = $this->createRemote('customers', $options);

        if (null !== $response && isset($response->error)) {
            return 0;
        }

        return $response->data->id;
    }

    public function updateCustomer($customer_id, $data): bool
    {
        $response = $this->updateRemote('customers/' . $customer_id, $data);

        return !(null !== $response && isset($response->success));
    }

    public function deleteCustomer($customer_id, $options): bool
    {
        $response = $this->deleteRemote('customers/' . $customer_id, $options);

        return !(null !== $response && isset($response->success));
    }

    public function getOrders($options = [])
    {
        $response = $this->getRemote('orders', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function getCustomFields($options = [])
    {
        $response = $this->getRemote('get_custom_fields', $options, 'wc-akaunting-for-woocommerce/v1');

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function getAttributeTerms($attribute_id, $options = [])
    {
        $response = $this->getRemote(sprintf('products/attributes/%d/terms', $attribute_id), $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function storeAttributeTerm($attribute_id, $data): int
    {
        $response = $this->createRemote(sprintf('products/attributes/%d/terms', $attribute_id), $data);

        if (null !== $response && isset($response->error)) {
            return 0;
        }

        return $response->data->id;
    }

    public function updateAttributeTerm($attribute_id, $term_id, $options): bool
    {
        $response = $this->updateRemote(sprintf('products/attributes/%d/terms/%d', $attribute_id, $term_id), $options);

        return !(null !== $response && isset($response->success));
    }

    public function deleteAttributeTerm($attribute_id, $term_id, $options = []): bool
    {
        $response = $this->deleteRemote(sprintf('products/attributes/%d/terms/%d', $attribute_id, $term_id), $options);

        return !(null !== $response && isset($response->success));
    }

    public function getProductAttributes($options = [])
    {
        $response = $this->getRemote('products/attributes', $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function storeProductAttribute($data): int
    {
        $response = $this->createRemote('products/attributes', $data);

        if (null !== $response && isset($response->error)) {
            return 0;
        }

        return $response->data->id;
    }

    public function updateProductAttribute($attribute_id, $options): bool
    {
        $response = $this->updateRemote('products/attributes' . $attribute_id, $options);

        return !(null !== $response && isset($response->success));
    }

    public function deleteProductAttribute($attribute_id, $options = []): bool
    {
        $response = $this->deleteRemote('products/attributes/' . $attribute_id, $options);

        return !(null !== $response && isset($response->success));
    }

    public function getProductVariations($product_id, $options = [])
    {
        $response = $this->getRemote(sprintf('products/%d/variations', $product_id), $options);

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }

    public function storeProductVariation($product_id, $data): int
    {
        $response = $this->createRemote(sprintf('products/%d/variations', $product_id), $data);

        if (null !== $response && isset($response->error)) {
            return 0;
        }

        return $response->data->id;
    }

    public function getSettings($group_id = null, $setting_id = null)
    {
        $path[] = 'settings';

        if (null !== $group_id) {
            $path[] = $group_id;
        }

        if (null !== $setting_id) {
            $path[] = $setting_id;
        }

        $response = $this->getRemote(implode('/', $path));

        if (null !== $response && isset($response->error)) {
            $response = new \stdClass();
            $response->meta = [];
            $response->data = [];
            return $response;
        }

        return $response;
    }


    /**
     * @return int
     */
    public function getPageLimit(): int
    {
        return $this->pageLimit;
    }

    /**
     * @param int $pageLimit
     */
    public function setPageLimit(int $pageLimit): void
    {
        $this->pageLimit = $pageLimit;
    }

    /**
     * @param Response                                    $lastResponse
     * @param                                             $method
     * @param                                             $action
     * @param \stdClass                                   $response
     */
    private function getResponseMetaData(Response $lastResponse,
                                         $method,
                                         $action,
                                         \stdClass $response): void
    {
        Log::debug('WC Integration::: Response Body: ' . print_r($lastResponse->getBody(), true));
        Log::debug('WC Integration::: Response Code: ' . print_r($lastResponse->getCode(), true));
        Log::debug('WC Integration::: Response Headers: ' . print_r($lastResponse->getHeaders(), true));

        $header = $lastResponse->getHeaders();
        if ($method === 'get'
            && (false === in_array($action, ['payment_gateways', 'get_custom_fields', 'products/attributes'])
                && false === strpos($action, 'settings'))
        ) {
            $response->meta = [
                'totalPage' => $header['X-WP-TotalPages'] ?? $header['x-wp-totalpages'],
                'totalItem' => $header['X-WP-Total'] ?? $header['x-wp-total'],
            ];
        }
    }
}
