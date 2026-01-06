<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ViewComposer extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(['components.documents.form.line-item'
        ], 'Modules\Inventory\Http\ViewComposers\DocumentItem');

        View::composer([
            'components.documents.template.line-item',
            'sales.invoices.print_classic',
            'sales.invoices.print_default',
            'sales.invoices.print_modern',
            'purchase.bills.print',
        ], 'Modules\Inventory\Http\ViewComposers\DocumentShow');

        View::composer(['partials.admin.navbar'
        ], 'Modules\Inventory\Http\ViewComposers\ItemReorderLevel');

        View::composer(['common.notifications.index'
        ], 'Modules\Inventory\Http\ViewComposers\ComponentReorderLevel');
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
