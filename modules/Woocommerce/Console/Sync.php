<?php

namespace Modules\Woocommerce\Console;

use App\Jobs\Install\DisableModule;
use App\Models\Common\Company;
use App\Models\Module\Module;
use App\Traits\Jobs;
use App\Traits\Modules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Modules\Woocommerce\Adapters\WooCommerceAdapter;
use Modules\Woocommerce\Jobs\SyncAttribute;
use Modules\Woocommerce\Jobs\SyncAttributeTerm;
use Modules\Woocommerce\Jobs\SyncCategory;
use Modules\Woocommerce\Jobs\SyncContact;
use Modules\Woocommerce\Jobs\SyncOrder;
use Modules\Woocommerce\Jobs\SyncPaymentMethod;
use Modules\Woocommerce\Jobs\SyncProduct;
use Modules\Woocommerce\Jobs\SyncTaxRate;
use Modules\Woocommerce\Jobs\SyncVariableProduct;

class Sync extends Command
{
    use Jobs;
    use Modules;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'woocommerce:sync {company?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync with woocommerce';

    /**
     * @var WooCommerceAdapter
     */
    private $adapter;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::debug('WC Integration::: Start sync...');

        if ($this->argument('company')) {
            $company_ids = $this->argument('company');
        } else {
            $company_ids = Module::allCompanies()->alias('woocommerce')->enabled()->pluck('company_id');
        }

        foreach ($company_ids as $company_id) {
            $company = Company::find($company_id);

            if (null === $company) {
                continue;
            }

            $company->makeCurrent();

            if (Cache::get(cache_prefix() . 'woocommerce_sync_running', false)) {
                $this->warn('WC Integration::: Sync is already running. Company id:' . $company_id);
                Log::warning('WC Integration::: Sync is already running. Company id:' . $company_id);
                continue;
            }

            Cache::set(cache_prefix() . 'woocommerce_sync_running', true, Date::now()->addHours(6));

            $timestamp = Date::now()->toDateTimeString();

            Log::debug('WC Integration::: Last sync time:' . setting('woocommerce.last_check', '---'));

            try {
                $this->adapter = new WooCommerceAdapter();

                $this->syncTaxes();
                $this->syncPaymentMethods();
                $this->syncCategories();
                $this->syncAttributes();
                $this->syncProducts();
                $this->syncCustomers();
                $this->syncOrders();
            } catch (\RuntimeException $e) {
                if (401 === $e->getCode() || 417 === $e->getCode()) {
                    $this->dispatch(new DisableModule('woocommerce', $company_id));
                    Log::info('WC Integration::: Module disabled');

                    $company->users->each(function ($user) {
                        if ($user->can('read-admin-panel')) {
                            $user->notify(new \Modules\Woocommerce\Notifications\DisableModule());
                        }
                    });
                }

                Log::info('WC Integration::: Runtime Exception');

                Cache::forget(cache_prefix() . 'woocommerce_sync_running');
                Company::forgetCurrent();

                continue;
            } catch (\Exception $e) {
                Log::error(
                    'WC Integration::: Company ID: ' . $company_id . ' Exception:' . basename($e->getFile()) . ':'
                    . $e->getLine() . ' - '
                    . $e->getCode() . ': ' . $e->getMessage()
                );

                report($e);

                Cache::forget(cache_prefix() . 'woocommerce_sync_running');
                Company::forgetCurrent();

                continue;
            }

            setting()->set('woocommerce.last_check', $timestamp);
            setting()->save();

            Cache::set(cache_prefix() . 'woocommerce_sync_running', false, Date::now()->addHours(6));
        }

        Company::forgetCurrent();
    }

    private function syncTaxes()
    {
        $this->info('Syncing taxes...');

        $page = 1;
        do {
            $taxRates = $this->adapter->getTaxRates(
                [
                    'page'     => $page,
                    'per_page' => $this->adapter->getPageLimit(),
                ]
            );

            foreach ($taxRates->data as $taxRate) {
                $this->info('Syncing tax rate: ' . $taxRate->id . '|' . $taxRate->name);

                $this->dispatchSync(new SyncTaxRate($taxRate));
            }

            $page++;
        } while (count($taxRates->data));
    }

    private function syncPaymentMethods()
    {
        $this->info('Syncing payment methods...');

        $paymentMethods = $this->adapter->getPaymentMethods();

        foreach ($paymentMethods->data as $paymentMethod) {
            $this->info('Syncing payment method: ' . $paymentMethod->title);

            $this->dispatchSync(new SyncPaymentMethod($paymentMethod));
        }
    }

    private function syncCategories()
    {
        $this->info('Syncing categories...');

        $page = 1;
        do {
            $categories = $this->adapter->getCategories(
                [
                    'page'     => $page,
                    'per_page' => $this->adapter->getPageLimit(),
                ]
            );

            foreach ($categories->data as $category) {
                $this->info('Syncing category: ' . $category->id . '|' . $category->name);

                $this->dispatchSync(new SyncCategory($category));
            }

            $page++;
        } while (count($categories->data));
    }

    private function syncProducts()
    {
        $this->info('Syncing products...');

        $page      = 1;
        $lastCheck = setting('woocommerce.last_check');

        do {
            $products = $this->adapter->getProducts(
                array_merge(
                    [
                        'page'     => $page,
                        'per_page' => $this->adapter->getPageLimit(),
                    ],
                    (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                )
            );

            foreach ($products->data as $product) {
                if ('variable' === $product->type) {
                    $this->info('Syncing variable products...');

                    if (false === $this->moduleIsEnabled('inventory')) {
                        Log::info(
                            "WC Integration::: Product ID: $product->id " .
                            "Variable Product ($product->name) is detected. " .
                            "Please use Inventory App to sync variable products."
                        );
                        continue;
                    }

//                    if (empty($product->variations)) {
//                        continue;
//                    }

                    $product->akaunting_items = $this->adapter->getProductVariations(
                        $product->id,
                        array_merge(
                            ['page' => 1, 'per_page' => 100],
//                            (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                        )
                    )->data;

                    $this->info('Syncing variable product: ' . $product->id . '|' . $product->name);

                    $this->dispatchSync(new SyncVariableProduct($product));
                } else {
                    $this->info('Syncing product: ' . $product->id . '|' . $product->name);

                    $this->dispatchSync(new SyncProduct($product));
                }
            }

            $page++;
        } while (count($products->data));
    }

    private function syncAttributes()
    {
        if (false === $this->moduleIsEnabled('inventory')) {
            return;
        }

        $this->info('Syncing attributes...');

        $attributes = $this->adapter->getProductAttributes();

        foreach ($attributes->data as $attribute) {
            $this->info('Syncing attribute: ' . $attribute->id . '|' . $attribute->name);

            $this->dispatchSync(new SyncAttribute($attribute));

            $this->info('Syncing attribute terms...');

            $page = 1;
            do {
                $terms = $this->adapter->getAttributeTerms(
                    $attribute->id,
                    [
                        'page'     => $page,
                        'per_page' => $this->adapter->getPageLimit(),
                    ]
                );

                foreach ($terms->data as $term) {
                    $this->info('Syncing attribute term: ' . $term->id . '|' . $term->name);

                    $term->attribute_id = $attribute->id;

                    $this->dispatchSync(new SyncAttributeTerm($term));
                }

                $page++;
            } while (count($terms->data));
        }
    }

    private function syncCustomers()
    {
        $this->info('Syncing customers...');

        $page      = 1;
        $lastCheck = setting('woocommerce.last_check');

        do {
            $customers = $this->adapter->getCustomers(
                array_merge(
                    [
                        'page'     => $page,
                        'per_page' => $this->adapter->getPageLimit(),
                    ],
                    (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : []
                )
            );

            foreach ($customers->data as $customer) {
                $this->info(
                    'Syncing customer: ' . $customer->id . '|' . (false === isset($customer->name) ?: $customer->name)
                );

                $this->dispatchSync(new SyncContact($customer));
            }

            $page++;
        } while (count($customers->data));
    }

    private function syncOrders()
    {
        $this->info('Syncing orders...');

        $page           = 1;
        $lastCheck      = setting('woocommerce.last_check');
        $orderStatusIds = setting('woocommerce.order_status_ids');

        do {
            $orders = $this->adapter->getOrders(
                array_merge(
                    [
                        'page'     => $page,
                        'per_page' => $this->adapter->getPageLimit(),
                    ],
                    (null !== $lastCheck) ? ['updated_since' => Date::parse($lastCheck)->toIso8601ZuluString()] : [],
                    (null !== $orderStatusIds) ? ['status' => implode(',', json_decode($orderStatusIds))] : []
                )
            );

            foreach ($orders->data as $order) {
                $this->info('Syncing order: ' . $order->id . '|' . $order->number);

                $this->dispatchSync(new SyncOrder($order));
            }

            $page++;
        } while (count($orders->data));
    }
}
