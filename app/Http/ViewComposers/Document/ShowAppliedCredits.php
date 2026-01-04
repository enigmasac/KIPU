<?php

namespace App\Http\ViewComposers\Document;

use App\Models\Document\DocumentTotal;
use Illuminate\View\View;
use App\Services\Credits;

class ShowAppliedCredits
{
    private Credits $credits;

    public function __construct(Credits $credits)
    {
        $this->credits = $credits;
    }

    public function compose(View $view)
    {
        $view_data = $view->getData();

        if (empty($view_data['invoice'])) {
            return;
        }

        $invoice = $view_data['invoice'];

        $appliedCredits = $this->credits->getAppliedCredits($invoice);
        $appliedDebits = $invoice->debit_notes_total;

        if (!$appliedCredits && !$appliedDebits) {
            return;
        }

        $invoice->totals_sorted = $invoice->totals_sorted->reject(function ($total) {
            return $total->code === 'total';
        });

        $invoice->totals_sorted->push(new DocumentTotal([
            'code'   => 'invoice_total',
            'name'   => 'Total factura',
            'amount' => $invoice->amount,
        ]));

        if ($appliedCredits) {
            $invoice->totals_sorted->push(new DocumentTotal([
                'code'   => 'applied_credits',
                'name'   => trans_choice('general.credit_notes', 2),
                'amount' => -$appliedCredits,
            ]));
        }

        if ($appliedDebits) {
            $invoice->totals_sorted->push(new DocumentTotal([
                'code'   => 'applied_debits',
                'name'   => trans_choice('general.debit_notes', 2),
                'amount' => $appliedDebits,
            ]));
        }

        $invoice->totals_sorted->push(new DocumentTotal([
            'code'   => 'total',
            'name'   => 'Total a pagar',
            'amount' => $invoice->amount_due,
        ]));

        $view->with('invoice', $invoice);
    }
}
