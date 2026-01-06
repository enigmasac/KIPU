<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use App\Models\Document\CreditNote;

class ShowCreateCreditNoteButton
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        if (empty($view_data['invoice'])) {
            return;
        }

        $invoice = $view_data['invoice'];

        $credit_notes_total_amount = CreditNote::where('invoice_id', $invoice->id)
            ->where('status', '!=', 'cancelled')
            ->where(function (Builder $query) {
                $query->whereNull('sunat_status')
                    ->orWhereRaw('LOWER(sunat_status) != ?', ['rechazado']);
            })
            ->sum('amount');

        $amount_exceeded = bccomp($credit_notes_total_amount, $invoice->amount_due) !== -1;

        $view->getFactory()->startPush(
            'edit_button_end',
            view(
                'partials.documents.invoice.create_button',
                compact('invoice', 'amount_exceeded')
            )
        );
    }
}
