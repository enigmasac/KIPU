<?php

namespace App\Services;

use App\Models\Document\Document;
use App\Models\Document\CreditsTransaction;

class Credits
{
    public function getAppliedCredits(Document $invoice): float
    {
        $applied_credits = CreditsTransaction::expense()->document($invoice->id)->sum('amount');

        if ($applied_credits <= 0) {
            $applied_credits = Document::where('invoice_id', $invoice->id)
                ->where('type', Document::CREDIT_NOTE_TYPE)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) {
                    $query->whereNull('sunat_status')
                        ->orWhere('sunat_status', '!=', 'rechazado');
                })
                ->sum('amount');
        }
        $precision = config('money.currencies.' . $invoice->currency_code . '.precision');

        return round($applied_credits, $precision);
    }

    public function getAvailableCredits(int $contactId)
    {
        $amounts = CreditsTransaction::selectRaw('type, SUM(amount) as amount')
            ->contact($contactId)
            ->groupBy('type')
            ->get();
        $income = $amounts->firstWhere('type', 'income');
        $income = $income ? $income->amount : 0;
        $expense = $amounts->firstWhere('type', 'expense');
        $expense = $expense ? $expense->amount : 0;

        return max($income - $expense, 0);
    }

    public function getTransactions(int $invoiceId)
    {
        return CreditsTransaction::expense()->document($invoiceId)->get();
    }

    public function getTransactionsCount(int $invoiceId)
    {
        return CreditsTransaction::expense()->document($invoiceId)->count();
    }
}
