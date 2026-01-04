<?php

namespace App\Http\ViewComposers\Document;

use App\Models\Document\Document;
use App\Models\Setting\Currency;
use Illuminate\View\View;
use App\Models\Document\DebitNote;

class PrefillDebitNote
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        if ($view_data['type'] !== DebitNote::TYPE) {
            return;
        }

        if (!empty($view_data['document'])) {
            return;
        }

        $invoice_id = request()->query('invoice_id', null);
        if ($invoice_id) {
            $invoice = Document::invoice()->findOrFail($invoice_id);

            $view->with('contact', $invoice->contact);
            $view->with('invoice_items', $invoice->items);
            $view->with('invoice_id', $invoice->id);
            $view->with('currency', $invoice->currency);
            $view->with('currency_code', $invoice->currency_code);
            $view->with('categoryId', $invoice->category_id);

            return;
        }

        if (!$bill_id = request()->query('bill', null)) {
            return;
        }

        $bill = Document::bill()->findOrFail($bill_id);

        $document = new \stdClass();
        $document->currency_code = $bill->currency_code;
        $document->currency_rate = $bill->currency_rate;
        $document->bill_id = $bill_id;
        $document->vendor_bills = $bill->contact->bills()
            ->whereIn('status', ['received', 'partial', 'paid'])
            ->pluck('document_number', 'id');

        $categoryId = $bill->category_id;

        $contact = $bill->contact;

        $currency_code = $bill->currency_code;
        $currency = Currency::where('code', $currency_code)->first();

        $view->with(compact('document', 'categoryId', 'contact', 'currency', 'currency_code'));
    }
}
