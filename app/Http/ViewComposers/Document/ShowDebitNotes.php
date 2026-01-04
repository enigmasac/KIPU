<?php

namespace App\Http\ViewComposers\Document;

use App\Models\Document\DebitNote;
use Illuminate\View\View;

class ShowDebitNotes
{
    public function compose(View $view)
    {
        $view_data = $view->getData();

        $invoice = $view_data['invoice'] ?? null;

        if (! $invoice) {
            return;
        }

        $debit_notes = DebitNote::where('invoice_id', $invoice->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('sunat_status')
                    ->orWhere('sunat_status', '!=', 'rechazado');
            })
            ->orderBy('issued_at')
            ->get();

        if ($debit_notes->isEmpty()) {
            return;
        }

        $debit_notes_total = $debit_notes->sum('amount');

        $view->getFactory()->startPush(
            'get_paid_end',
            view('partials.documents.invoice.debit_notes', compact('invoice', 'debit_notes', 'debit_notes_total'))
        );
    }
}
