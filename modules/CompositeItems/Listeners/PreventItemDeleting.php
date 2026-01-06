<?php

namespace Modules\CompositeItems\Listeners;

use App\Events\Common\RelationshipCounting as Event;
use App\Models\Common\Item;

class PreventItemDeleting
{
    public function handle(Event $event)
    {
        if (!$event->record->model instanceof Item) {
            return;
        }

        $event->record->relationships['composite_items'] = 'composite-items::general.name';
    }
}
