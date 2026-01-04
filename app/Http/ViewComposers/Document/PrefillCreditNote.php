<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\View\View;
use App\Models\Document\CreditNote;
use App\Models\Document\Document;

class PrefillCreditNote
{
    /**
     * Bind data to the view.
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        if (($view_data['type'] ?? '') !== CreditNote::TYPE) {
            return;
        }

        if (empty($invoice_id = request()->query('invoice_id', request()->query('invoice', null)))) {
            return;
        }

        $invoice = Document::invoice()->findOrFail($invoice_id);

        $view->with('contact', $invoice->contact);
        $view->with('invoice_items', $invoice->items);
        $view->with('invoice_id', $invoice->id);
    }
}
