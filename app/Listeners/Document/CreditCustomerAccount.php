<?php

namespace App\Listeners\Document;

use App\Events\Document\DocumentMarkedSent as Event;
use App\Traits\Jobs;
use App\Jobs\Document\Credits\CreateCreditsTransaction;
use App\Models\Document\CreditNote;

class CreditCustomerAccount
{
    use Jobs;

    public function handle($event)
    {
        if ($event->document->type !== CreditNote::TYPE) {
            return;
        }

        if ($event->document->credit_customer_account) {
            $this->ajaxDispatch(new CreateCreditsTransaction($event->document));
        }
    }
}
