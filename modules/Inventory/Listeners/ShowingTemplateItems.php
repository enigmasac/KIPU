<?php

namespace Modules\Inventory\Listeners;

use App\Models\Document\Document;
use App\Traits\Modules;
use Modules\PrintTemplate\Events\ShowingTemplateItems as Event;

class ShowingTemplateItems
{
    use Modules;

    /**
     * Handle the event.
     *
     * @param  Event  $event
     * @return void
     */
    public function handle(Event $event)
    {
        if ($this->moduleIsDisabled('inventory') || $this->moduleIsDisabled('print-template')) {
            return;
        }

        if (in_array($event->print_template->type, [Document::INVOICE_TYPE, Document::BILL_TYPE])) {
            $event->items['general.items']['inventory->unit'] = 'inventory::general.unit';
        }
    }
}
