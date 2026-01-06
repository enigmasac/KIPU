<?php

namespace App\Http\ViewComposers\Document;

use App\Models\Document\CreditNote;
use Illuminate\View\View;

class ShowCreditNotes
{
    public function compose(View $view)
    {
        $view_data = $view->getData();

        $invoice = $view_data['invoice'] ?? null;

        if (!$invoice) {
            return;
        }

        $credit_notes = CreditNote::where('invoice_id', $invoice->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('sunat_status')
                    ->orWhereRaw('LOWER(sunat_status) != ?', ['rechazado']);
            })
            ->orderBy('issued_at')
            ->get();

        if ($credit_notes->isEmpty()) {
            return;
        }

        $credit_notes_total = $credit_notes->sum('amount');

        $view->getFactory()->startPush(
            'get_paid_end',
            view('partials.documents.invoice.credit_notes', [
                'invoice' => $invoice,
                'credit_notes' => $credit_notes,
                'credit_notes_total' => $credit_notes_total,
            ])
        );
    }
}
