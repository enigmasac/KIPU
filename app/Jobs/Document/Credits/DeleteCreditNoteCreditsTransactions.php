<?php

namespace App\Jobs\Document\Credits;

use App\Abstracts\Job;
use App\Models\Document\Document;

class DeleteCreditNoteCreditsTransactions extends Job
{
    protected $credit_note;

    public function __construct(Document $credit_note)
    {
        $this->credit_note = $credit_note;
    }

    public function handle(): bool
    {
        $this->credit_note->credits_transactions()->delete();

        return true;
    }
}
