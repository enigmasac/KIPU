<?php

namespace App\Listeners\Document;

use App\Events\Document\DocumentCreated as Event;
use App\Traits\Documents;

class IncreaseNextDocumentNumber
{
    use Documents;

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(Event $event)
    {
        // SUNAT Compliance: Deferred Numbering.
        // Do not increase the official counter for drafts.
        if ($event->document->status === 'draft') {
            return;
        }

        // Update next document number
        $this->increaseNextDocumentNumber($event->document->type);
    }
}
