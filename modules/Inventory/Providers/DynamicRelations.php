<?php

namespace Modules\Inventory\Providers;

use App\Models\Common\Item;
use App\Models\Document\DocumentItem;
use Modules\Inventory\Models\Common\Item as CoreItem;
use Illuminate\Support\ServiceProvider;

class DynamicRelations extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Item::resolveRelationUsing('inventory', function ($item) {
            return $item->belongsTo('Modules\Inventory\Models\Item', 'id', 'item_id');
        });

        CoreItem::resolveRelationUsing('inventory', function ($item) {
            return $item->belongsTo('Modules\Inventory\Models\Item', 'id', 'item_id');
        });

        DocumentItem::resolveRelationUsing('inventory', function ($item) {
            return $item->belongsTo('Modules\Inventory\Models\Item', 'item_id', 'item_id');
        });
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
