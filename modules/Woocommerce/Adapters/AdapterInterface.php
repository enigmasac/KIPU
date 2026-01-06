<?php

namespace Modules\Woocommerce\Adapters;

interface AdapterInterface
{
    /**
     * @param string $action
     *
     * @return mixed
     */
    public function getRemote($action);

    /**
     * @param array $params
     *
     * @return object
     */
    public function getTaxRates($params);

    /**
     * @param array $params
     *
     * @return object
     */
    public function getPaymentMethods($params);

    /**
     * @param array $params
     *
     * @return int
     */
    public function storeTaxRates($params);

    /**
     * @param array $params
     * @param int $tax_id
     *
     * @return bool
     */
    public function updateTaxRates($tax_id, $params);

   /**
     * @param array $params
     * @param int $tax_id
     *
     * @return bool
     */
    public function deleteTaxRates($tax_id, $params);

    /**
     * @param array $params
     *
     * @return object
     */
    public function getCategories($params);

    /**
     * @param array $params
     *
     * @return int
     */
    public function storeCategory($params);

    /**
     * @param array $params
     * @param int $category_id
     *
     * @return bool
     */
    public function updateCategory($category_id, $params);

    /**
     * @param array $params
     * @param int $category_id
     *
     * @return bool
     */
    public function deleteCategory($category_id, $params);

    /**
     * @param array $params
     *
     * @return object
     */
    public function getProducts($params);

    /**
     * @param array $params
     *
     * @return int
     */
    public function storeProduct($params);

    /**
     * @param array $params
     * @param int $product_id
     *
     * @return bool
     */
    public function updateProduct($product_id, $params);

    /**
     * @param array $params
     * @param int $product_id
     *
     * @return bool
     */
    public function deleteProduct($product_id, $params);

    /**
     * @param array $params
     *
     * @return object
     */
    public function getCustomers($params);

    /**
     * @param array $params
     *
     * @return int
     */
    public function storeCustomer($params);

    /**
     * @param array $params
     * @param int $customer_id
     *
     * @return bool
     */
    public function updateCustomer($customer_id, $params);

    /**
     * @param array $params
     * @param int $customer_id
     *
     * @return bool
     */
    public function deleteCustomer($customer_id, $params);

    /**
     * @param array $params
     *
     * @return object
     */
    public function getOrders($params);

}
