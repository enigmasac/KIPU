<?php

namespace Modules\Woocommerce\Providers;

use App\Models\Common\Contact;
use App\Models\Common\Item;
use App\Models\Document\Document;
use App\Models\Setting\Category;
use App\Models\Setting\Tax;
use Illuminate\Support\ServiceProvider;
use Modules\Woocommerce\Observers\Common\Contact as ContactObserver;
use Modules\Woocommerce\Observers\Common\Item as ItemObserver;
use Modules\Woocommerce\Observers\Setting\Category as CategoryObserver;
use Modules\Woocommerce\Observers\Setting\Tax as TaxObserver;
use Modules\Woocommerce\Observers\Income\Invoice as InvoiceObserver;
use Modules\Woocommerce\Observers\Inventory\Item as InventoryItem;
use Modules\Woocommerce\Observers\Inventory\ItemGroup as InventoryItemGroup;
use Modules\Woocommerce\Observers\Inventory\ItemGroupItem as InventoryItemGroupItem;
use Modules\Woocommerce\Observers\Inventory\Variant as InventoryVariant;
use Modules\Woocommerce\Observers\Inventory\VariantValue as InventoryVariantValue;

class Observer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        if (module('inventory')) {
            \Modules\Inventory\Models\VariantValue::observe(InventoryVariantValue::class);
            \Modules\Inventory\Models\ItemGroupItem::observe(InventoryItemGroupItem::class);
        }
        $argv = request()->server('argv');

        if (!empty($argv[1]) && $argv[1] === 'woocommerce:sync' && app()->runningInConsole()) {
            return;
        }

        Contact::observe(ContactObserver::class);
        Item::observe(ItemObserver::class);
        if (module('inventory')) {
            \Modules\Inventory\Models\Item::observe(InventoryItem::class);
            \Modules\Inventory\Models\ItemGroup::observe(InventoryItemGroup::class);
            \Modules\Inventory\Models\Variant::observe(InventoryVariant::class);
        }
        Category::observe(CategoryObserver::class);
        Tax::observe(TaxObserver::class);
        Document::observe(InvoiceObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
