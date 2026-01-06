<?php

namespace Modules\Woocommerce\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider as Provider;
use Modules\Woocommerce\Console\Sync;

class Main extends Provider
{
    /**
     * @var array
     */
    protected $middleware = [
        'Woocommerce' => [
            'woocommerce-authenticate' => 'Authenticate',
        ],
    ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslations();
        $this->loadMigrations();
        $this->loadViews();
        $this->loadCommands();
        $this->loadMiddleware($this->app['router']);
    }

    /**
     * Load the middlewares.
     *
     * @param  Router $router
     * @return void
     */
    public function loadMiddleware(Router $router)
    {
        foreach ($this->middleware as $module => $middlewares) {
            foreach ($middlewares as $name => $middleware) {
                $class = "Modules\\{$module}\\Http\\Middleware\\{$middleware}";

                $router->aliasMiddleware($name, $class);
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->loadRoutes();
    }

    /**
     * Load views.
     *
     * @return void
     */
    public function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'woocommerce');
    }

    /**
     * Load translations.
     *
     * @return void
     */
    public function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'woocommerce');
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
            'api.php',
            'signed.php',
        ];

        foreach ($routes as $route) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/' . $route);
        }
    }

    /**
     * Load routes.
     *
     * @return void
     */
    public function loadCommands()
    {
        $this->commands(Sync::class);

        $this->app->booted(function () {
            $expression = env('SCHEDULE_EXPRESSION_ECOMMERCE', '35 * * * *'); // hourly at minute 35

            app(Schedule::class)->command(Sync::class)->cron($expression);
        });
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
