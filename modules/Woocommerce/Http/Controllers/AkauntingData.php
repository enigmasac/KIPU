<?php

namespace Modules\Woocommerce\Http\Controllers;

use App\Models\Common\Contact;
use App\Models\Common\Item;
use App\Models\Setting\Category;
use App\Traits\Contacts;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Models\WooCommerceIntegration;

class AkauntingData extends Controller
{
    use Contacts;

    private $adapter;

    public function __construct()
    {
        $this->adapter = new WooCommerceAdapter();
    }

    public function count()
    {
        if (false === (bool) setting('woocommerce.two_way_create_update', false)) {
            return response()->json(
                [
                    'success' => false,
                    'error'   => true,
                    'count'   => 0,
                    'steps'   => [],
                    'message' => trans('woocommerce::general.error.nothing_to_sync_akaunting')
                ]
            );
        }

        $total = 0;
        $steps = [];
        $success = true;
        $error   = false;
        $message = '';

        $this->getCategories($steps, $total);
        $this->getProducts($steps, $total);
        $this->getCustomers($steps, $total);

        Cache::set(cache_prefix() . 'woocommerce_sync_akaunting_total', $total, Date::now()->addHours(6));
        Cache::set(cache_prefix() . 'woocommerce_sync_akaunting_count', 0, Date::now()->addHours(6));

        if (empty($steps)) {
            $success = false;
            $error   = true;
            $message = trans('woocommerce::general.error.nothing_to_sync_akaunting');
        }

        return response()->json(
            [
                'success' => $success,
                'error'   => $error,
                'count'   => $total,
                'steps'   => $steps,
                'message' => $message
            ]
        );
    }

    private function getCategories(&$steps, &$total)
    {
        $syncWoocommerce = [];

        $wooCategories = WooCommerceIntegration::where('item_type', Category::class)->get();

        foreach ($wooCategories as $category) {
            $syncWoocommerce[] = $category->item_id;

            $syncWoocommerce = array_merge($syncWoocommerce);
        }

        $categories = Category::whereNotIn('id', $syncWoocommerce)->get();

        foreach ($categories as $category) {
            if ('item' !== $category->type) {
                continue;
            }

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.categories', 1),
                        'value' => $category->name,
                    ]
                ),
                'url'  => route('woocommerce.sync.akaunting.categories', $category->id),
                'id'   => $category->id,
            ];
            $total++;
        }
    }

    public function syncCategory($id)
    {
        $category = Category::find($id);

        $params = [
            'name'  => $category->name,
        ];

        $categoryId = $this->adapter->storeCategory($params);

        if (0 === $categoryId) {
            Log::error('Akaunting to WC Integration::: Category is not synced:' . print_r($category, true));
            return response()->json(
                [
                    'error'    => true,
                    'success'  => true,
                    'finished' => false,
                    'message'  => trans('woocommerce::general.error.category_sync_error', ['id' => $category->id])
                ]
            );
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $categoryId;
        $integration->item_id           = $category->id;
        $integration->item_type         = Category::class;

        $integration->save();

        $json = [
            'error'   => false,
            'success'  => true,
            'finished' => false,
            'message'  => ''
        ];

        if ($this->isFinished()) {
            $json['finished'] = true;
            $json['message']  = trans(
                'woocommerce::general.finished',
                ['type' => trans_choice('woocommerce::general.types.categories', 2)]
            );
        }

        return response()->json($json);
    }

    public function getProducts(&$steps, &$total)
    {
        $syncWoocommerce = [];

        $wooProducts = WooCommerceIntegration::where(['item_type' => Item::class, 'company_id' => company_id()])->get();

        foreach ($wooProducts as $product) {
            $syncWoocommerce[] = $product->item_id;

            $syncWoocommerce = array_merge($syncWoocommerce);
        }

        $items = Item::whereNotIn('id', $syncWoocommerce)->get();

        foreach ($items as $item) {
            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type'  => trans_choice('woocommerce::general.types.items', 1),
                        'value' => $item->name,
                    ]
                ),
                'url'  => route('woocommerce.sync.akaunting.products', $item->id),
                'id'   => $item->id,
            ];
            $total++;
        }
    }

    public function syncProducts($id)
    {
        $item = Item::find($id);

        $categoryIntegration = WooCommerceIntegration::where(
            [
                'company_id' => company_id(),
                'item_id'   => $item->category_id,
                'item_type' => Category::class,
            ]
        )->first();

        $status = $item->enabled == true ? 'publish' : 'private';

        $params = array_merge(
            null !== $categoryIntegration ? ['categories' => [['id' => $categoryIntegration->woocommerce_id]]] : [],
            [
                'name'          => $item->name,
                'regular_price' => (string)$item->sale_price,
                'description'   => $item->description ?? '',
                'status'        => $status,
            ]
        );

        $productId = $this->adapter->storeProduct($params);

        if (0 === $productId) {
            Log::error('Akaunting to WC Integration::: Product is not synced:' . print_r($item, true));
            return response()->json(
                [
                    'error'    => true,
                    'success'  => true,
                    'finished' => false,
                    'message'  => trans('woocommerce::general.error.item_sync_error', ['id' => $item->id])
                ]
            );
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $productId;
        $integration->item_id           = $item->id;
        $integration->item_type         = Item::class;

        $integration->save();

        $json = [
            'error'   => false,
            'success'  => true,
            'finished' => false,
            'message'  => ''
        ];

        if ($this->isFinished()) {
            $json['finished'] = true;
            $json['message']  = trans(
                'woocommerce::general.finished',
                ['type' => trans_choice('woocommerce::general.types.items', 2)]
            );
        }

        return response()->json($json);
    }

    private function getCustomers(&$steps, &$total)
    {
        $syncWoocommerce = [];

        $wooContacts = WooCommerceIntegration::where(['item_type' => Contact::class, 'company_id' => company_id()])->get();

        foreach ($wooContacts as $contact) {
            $syncWoocommerce[] = $contact->item_id;

            $syncWoocommerce = array_merge($syncWoocommerce);
        }

        $customers = Contact::whereNotIn('id', $syncWoocommerce)->get();

        foreach ($customers as $customer) {
            if (false === in_array($customer->type, $this->getCustomerTypes()) || empty($customer->email)) {
                continue;
            }

            $steps[] = [
                'text' => trans(
                    'woocommerce::general.sync_text',
                    [
                        'type' => trans_choice('woocommerce::general.types.contacts', 2),
                        'value' => $customer->name,
                    ]
                ),
                'url'  => route('woocommerce.sync.akaunting.contacts', $customer->id),
                'id'   => $customer->id,
            ];
            $total++;
        }
    }

    public function syncContact($id)
    {
        $contact = Contact::find($id);

        $generateUsernameResponse = $this->adapter->getSettings('account', 'woocommerce_registration_generate_username');
        $generateUsername = true;
        if ($generateUsernameResponse->data && $generateUsernameResponse->data->value === 'yes') {
            $generateUsername = false;
        }

        $generatePasswordResponse = $this->adapter->getSettings('account', 'woocommerce_registration_generate_password');
        $generatePassword = true;
        $password = Str::random();
        if ($generatePasswordResponse->data && $generatePasswordResponse->data->value === 'yes') {
            $generatePassword = false;
        }

        $name = explode(' ', $contact->name);
        $username = explode('@', $contact->email)[0] . '-' . $contact->id;
        $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);

        $params = array_merge(
            $generateUsername ? ['username' => $username] : [],
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
            Log::error('Akaunting to WC Integration::: Contact is not synced:' . print_r($contact, true));
            return response()->json(
                [
                    'error'    => true,
                    'success'  => true,
                    'finished' => false,
                    'message'  => trans('woocommerce::general.error.contact_sync_error', ['id' => $contact->id])
                ]
            );
        }

        $integration                    = new WooCommerceIntegration();
        $integration->company_id        = company_id();
        $integration->woocommerce_id    = $customerId;
        $integration->item_id           = $contact->id;
        $integration->item_type         = Contact::class;

        $integration->save();

        $json = [
            'error'   => false,
            'success'  => true,
            'finished' => false,
            'message'  => ''
        ];

        if ($this->isFinished()) {
            $json['finished'] = true;
            $json['message']  = trans(
                'woocommerce::general.finished',
                ['type' => trans_choice('woocommerce::general.types.contacts', 2)]
            );
        }

        return response()->json($json);
    }

    private function isFinished(): bool
    {
        $syncCount = Cache::get(cache_prefix() . 'woocommerce_sync_akaunting_count', 0) + 1;

        Cache::set(cache_prefix() . 'woocommerce_sync_akaunting_count', $syncCount, Date::now()->addHours(6));

        return !($syncCount !== (int) Cache::get(cache_prefix() . 'woocommerce_sync_akaunting_total', 0));
    }
}
