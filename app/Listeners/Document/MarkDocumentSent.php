<?php

namespace App\Listeners\Document;

use App\Events\Document\DocumentMarkedSent;
use App\Events\Document\DocumentSent;
use App\Jobs\Document\CreateDocumentHistory;
use App\Traits\Jobs;

class MarkDocumentSent
{
    use Jobs;

    public function handle(DocumentMarkedSent|DocumentSent $event): void
    {
        if (!in_array($event->document->status, ['partial', 'paid'])) {
            // Update issued_at to now if it was a draft, required for valid emission date
            if ($event->document->status === 'draft') {
                $event->document->issued_at = now();
                // Also update due_at if it was same as issued_at or handle relative due dates? 
                // For now, let's keep it simple as requested. BUt often due_at is calculated from issued_at.
                // If due_at < issued_at, it might be weird. 
                // Let's just update issued_at as requested.
            }

            $event->document->status = 'sent';

            //This control will be removed when approval status is added to documents.
            if ($event->document->amount == 0) {
                $event->document->status = 'paid';
            }

            $event->document->save();
        }

        $this->dispatch(new CreateDocumentHistory($event->document, 0, $this->getDescription($event)));
    }

    public function getDescription(DocumentMarkedSent|DocumentSent $event): string
    {
        $type_text = '';

        if ($alias = config('type.document.' . $event->document->type . '.alias', '')) {
            $type_text .= $alias . '::';
        }

        $type_text .= 'general.' . config('type.document.' . $event->document->type . '.translation.prefix');

        $type = trans_choice($type_text, 1);

        $message = ($event instanceof DocumentMarkedSent) ? 'marked_sent' : 'email_sent';

        return trans('documents.messages.' . $message, ['type' => $type]);
    }
}
