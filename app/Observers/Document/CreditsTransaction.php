<?php

namespace App\Observers\Document;

use App\Abstracts\Observer;
use App\Jobs\Document\CreateDocumentHistory;
use App\Models\Document\CreditsTransaction as Model;
use App\Traits\Jobs;
use App\Services\Credits;

class CreditsTransaction extends Observer
{
    use Jobs;

    public function deleted(Model $credits_transaction)
    {
        if (empty($credits_transaction->document_id)) {
            return;
        }

        if ($credits_transaction->type == 'expense') {
            $this->updateInvoice($credits_transaction);
        } else {
            $this->createCreditNoteHistory($credits_transaction);
        }
    }

    protected function updateInvoice($credits_transaction)
    {
        $invoice = $credits_transaction->invoice;

        if (!$invoice) {
            return;
        }

        $credits_transactions_count = (new Credits())->getTransactionsCount($invoice->id);
        $invoice->status = (($invoice->transactions->count() + $credits_transactions_count) > 0) ? 'partial' : 'sent';

        $invoice->save();

        $this->dispatch(new CreateDocumentHistory($invoice, 0, $this->getDescription($credits_transaction)));
    }

    protected function getDescription($credits_transaction)
    {
        $amount = money((double) $credits_transaction->amount, (string) $credits_transaction->currency_code, true)->format();

        return trans('messages.success.deleted', ['type' => $amount . ' ' . trans_choice('general.credit_notes', 1)]);
    }

    protected function createCreditNoteHistory($credits_transaction)
    {
        $credit_note = $credits_transaction->credit_note;

        if (!$credit_note) {
            return;
        }

        $amount = money((double) $credits_transaction->amount, (string) $credits_transaction->currency_code, true)->format();
        $history_desc =  trans('credit_notes.credit_cancelled', ['amount' => $amount]);

        $this->dispatch(new CreateDocumentHistory($credit_note, 0, $history_desc));
    }
}
