<?php

namespace Modules\Woocommerce\Listeners;

use App\Events\Menu\SettingsCreated as Event;
use App\Traits\Modules;
use App\Traits\Permissions;

class ShowInSettingsMenu
{
    use Modules, Permissions;

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        if (!$this->moduleIsEnabled('woocommerce')) {
            return;
        }

        $title = trans('woocommerce::general.name');

        if ($this->canAccessMenuItem($title, 'read-woocommerce-settings')) {
            $event->menu->route('woocommerce.edit', $title, [], 100, ['icon' => 'custom-woo', 'search_keywords' => trans('woocommerce::general.description')]);
        }
    }
}
