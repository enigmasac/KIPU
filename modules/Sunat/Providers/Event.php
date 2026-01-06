<?php

namespace Modules\Sunat\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\Document\DocumentMarkedSent;
use App\Events\Menu\SettingsCreated;
use Modules\Sunat\Listeners\AddToSettingsMenu;
use Modules\Sunat\Listeners\EmitOnDocumentSent;

class Event extends Provider
{
    /**
     * The event listener mappings for the module.
     *
     * @var array
     */
    protected $listen = [
        SettingsCreated::class => [
            AddToSettingsMenu::class,
        ],
        DocumentMarkedSent::class => [
            EmitOnDocumentSent::class,
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false; // We're explicitly defining listeners now
    }
}
