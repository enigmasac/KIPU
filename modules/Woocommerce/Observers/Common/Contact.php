<?php

namespace Modules\Woocommerce\Observers\Common;

use App\Abstracts\Observer;
use App\Models\Common\Contact as Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class Contact extends Observer
{
    /**
     * @var WooCommerceAdapter
     */
    private $adapter;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->adapter = new WooCommerceAdapter();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $contact
     *
     * @return void
     */
    public function created(Model $contact)
    {
        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        if (empty(setting('woocommerce.consumer_secret', '')) || empty(setting('woocommerce.consumer_key', ''))) {
            flash(trans('woocommerce::general.form.not_transferred'));
            return;
        }

        if (null === $contact->email) {
            return;
        }

        if (WooCommerceIntegration::where(['item_id' => $contact->id, 'item_type' => Model::class, 'company_id' => company_id()])->first()) {
            return;
        }

        $generateUsernameResponse = $this->adapter->getSettings('account', 'woocommerce_registration_generate_username');
        $generateUsername = true;
        if ($generateUsernameResponse->data && $generateUsernameResponse->data->value === 'yes') {
            $generateUsername = false;
        }

        $generatePasswordResponse = $this->adapter->getSettings('account', 'woocommerce_registration_generate_password');
        $generatePassword = true;
        $password = Str::random();

        if ("true" === request()->get('create_user')) {
            $password = request()->get('password', $password);
        }

        if ($generatePasswordResponse->data && $generatePasswordResponse->data->value === 'yes') {
            $generatePassword = false;
        }

        $name = explode(' ', $contact->name);

        $params = array_merge(
            $generateUsername ? ['username' => explode('@', $contact->email)[0] . '-' . $contact->id] : [],
            $generatePassword ? ['password' => $password] : [],
            [
                'email'      => $contact->email,
                'first_name' => $name[0],
                'last_name'  => $name[1] ?? '',
                'role'       => 'customer',
                'billing'    => [
                    'first_name' => $name[0],
                    'last_name'  => $name[1] ?? '',
                    'company'    => '',
                    'address_1'  => '',
                    'address_2'  => '',
                    'city'       => '',
                    'state'      => '',
                    'postcode'   => '',
                    'country'    => '',
                    'email'      => $contact->email,
                    'phone'      => $contact->phone ?: '',
                ],
                'shipping'   => [
                    'first_name' => '',
                    'last_name'  => '',
                    'company'    => '',
                    'address_1'  => '',
                    'address_2'  => '',
                    'city'       => '',
                    'state'      => '',
                    'postcode'   => '',
                    'country'    => '',
                ],
            ]
        );

        $customerId = $this->adapter->storeCustomer($params);

        if (0 === $customerId) {
            Log::error('WC Integration::: Contact is not synced:' . print_r($contact, true));
            return;
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $customerId;
        $integration->item_id           = $contact->id;
        $integration->item_type         = Model::class;

        $integration->save();
    }

    /**
     * Listen to the created event.
     *
     * @param Model $contact
     *
     * @return void
     */
    public function updated(Model $contact)
    {
        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return;
        }

        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        if (empty(setting('woocommerce.consumer_secret', '')) || empty(setting('woocommerce.consumer_key', ''))) {
            flash(trans('woocommerce::general.form.not_transferred'));
        }

        $integration = WooCommerceIntegration::where(['item_id' => $contact->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        $name = explode(' ', $contact->name);

        $params = [
            'first_name'  => $name[0],
            'last_name'   => isset($name[1]) ? $name[1] : '',
            'email'       => $contact->email,
            'billing'     => [
                'phone'   => $contact->phone ? $contact->phone : ''
            ]
        ];

        $this->adapter->updateCustomer(
            $integration->woocommerce_id,
            $params
        );


        $integration->save();
    }

    /**
     * Listen to the deleted event.
     *
     * @param Model $contact
     *
     * @return void
     */
    public function deleted(Model $contact)
    {
        if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
            return;
        }

        $integration = WooCommerceIntegration::where(['item_id' => $contact->id, 'item_type' => Model::class, 'company_id' => company_id()])->first();

        if (null === $integration) {
            return;
        }

        if ((bool) setting('woocommerce.two_way_delete', false)) {
            $this->adapter->deleteCustomer($integration->woocommerce_id, []);
        }

        $integration->delete();
    }
}
