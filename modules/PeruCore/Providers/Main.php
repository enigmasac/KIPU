<?php

namespace Modules\PeruCore\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as Provider;
use App\Models\Common\Contact;
use App\Models\Common\Item;
use App\Models\Setting\Tax;
use App\Models\Document\Document;
use App\Models\Setting\Currency;
use Modules\PeruCore\Observers\ContactObserver;
use Modules\PeruCore\Observers\ItemObserver;
use Modules\PeruCore\Observers\TaxObserver;
use Modules\PeruCore\Observers\DocumentObserver;
use Modules\PeruCore\Observers\CurrencyObserver;

class Main extends Provider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadConfig();
        $this->loadRoutes();
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViews();
        $this->loadViewComponents();
        $this->loadMigrations();
        $this->loadObservers();
    }

    /**
     * Load observers.
     *
     * @return void
     */
    public function loadObservers()
    {
        Contact::observe(ContactObserver::class);
        Tax::observe(TaxObserver::class);
        Item::observe(ItemObserver::class);
        Document::observe(DocumentObserver::class);
        Currency::observe(CurrencyObserver::class);
    }

    /**
     * Load views.
     *
     * @return void
     */
    public function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'peru-core');
    }

    /**
     * Load view components.
     *
     * @return void
     */
    public function loadViewComponents()
    {
        Blade::componentNamespace('Modules\PeruCore\View\Components', 'peru-core');
    }

    /**
     * Load migrations.
     *
     * @return void
     */
    public function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Load config.
     *
     * @return void
     */
    public function loadConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'peru-core');
        $this->mergeConfigFrom(__DIR__ . '/../Config/sunat.php', 'peru-core.sunat');
    }

    /**
     * Load routes.
     *
     * @return void
     */
    public function loadRoutes()
    {
        if (app()->routesAreCached()) {
            return;
        }

        $routes = [
            'admin.php',
            'portal.php',
        ];

        foreach ($routes as $route) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/' . $route);
        }
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