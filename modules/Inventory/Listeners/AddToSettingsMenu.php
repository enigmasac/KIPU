<?php

namespace Modules\Inventory\Listeners;

use App\Events\Menu\SettingsCreated as Event;
use App\Traits\Modules;
use App\Traits\Permissions;

class AddToSettingsMenu
{
    use Permissions, Modules;

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if (! $this->moduleIsEnabled('inventory')) {
            return;
        }

        $title = trim(trans('inventory::general.name'));
        if ($this->canAccessMenuItem($title, 'read-inventory-settings')) {
            $event->menu->route('inventory.settings.edit', $title, [], 201, ['icon' => 'inventory_2', 'search_keywords' => trans('inventory::general.description')]);
        }
    }
}
