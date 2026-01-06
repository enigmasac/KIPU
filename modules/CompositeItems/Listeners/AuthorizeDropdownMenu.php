<?php

namespace Modules\CompositeItems\Listeners;

use App\Events\Menu\ItemAuthorizing as Event;
use App\Traits\Modules;

class AuthorizeDropdownMenu
{
    use Modules;

    public function handle(Event $event)
    {
        if ($this->moduleIsDisabled('composite-items') && $this->moduleIsDisabled('inventory')) {
            return;
        }

        if ($event->item->title === trim(trans('inventory::general.menu.inventory'))) {
            $event->item->permissions[] = 'read-composite-items-composite-items';
        }
    }
}
