<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\View\View;
use App\Models\Document\DebitNote;

class ShowCreateDebitNoteButton
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        $bill = $view_data['bill'] ?? null;
        $invoice = $view_data['invoice'] ?? null;

        if (! $bill && ! $invoice) {
            return;
        }

        if ($bill) {
            $debit_notes_total_amount = DebitNote::where('status', 'sent')
                ->where('bill_id', $bill->id)
                ->sum('amount');

            $amount_exceeded = bccomp($debit_notes_total_amount, $bill->amount_due) !== -1;

            $view->getFactory()->startPush(
                'edit_button_end',
                view('partials.documents.bill.create_button', compact('bill', 'amount_exceeded'))
            );

            return;
        }

        $view->getFactory()->startPush(
            'edit_button_end',
            view('partials.documents.invoice.create_debit_note_button', compact('invoice'))
        );
    }
}
