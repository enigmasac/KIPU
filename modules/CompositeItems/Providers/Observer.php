<?php

namespace Modules\CompositeItems\Providers;

use App\Models\Common\Item;

use Illuminate\Support\ServiceProvider;

class Observer extends ServiceProvider
{
    public function boot()
    {
        Item::observe('Modules\CompositeItems\Observers\Item');
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
