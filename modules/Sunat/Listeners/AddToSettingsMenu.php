<?php

namespace Modules\Sunat\Listeners;

use App\Events\Menu\SettingsCreated;

class AddToSettingsMenu
{
    /**
     * Handle the event.
     *
     * @param  SettingsCreated $event
     * @return void
     */
    public function handle(SettingsCreated $event)
    {
        $event->menu->route('sunat.configuration.index', 'SUNAT', [], 50, ['icon' => 'description']);
    }
}
