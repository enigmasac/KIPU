<?php

namespace App\Providers;

use App\Models\Banking\Transaction;
use App\Models\Document\Document;
use Illuminate\Support\ServiceProvider as Provider;

class Observer extends Provider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Transaction::observe('App\Observers\Transaction');
        Document::observe('App\Observers\Document');
        \App\Models\Document\CreditsTransaction::observe('App\Observers\Document\CreditsTransaction');
    }
}
