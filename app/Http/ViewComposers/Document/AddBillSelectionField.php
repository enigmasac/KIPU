<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\View\View;
use App\Models\Document\DebitNote;
use App\Models\Common\Contact;

class AddBillSelectionField
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

        if (($view_data['type'] ?? '') !== DebitNote::TYPE) {
            return;
        }

        $route = request()->route();
        if ($route && ! str_contains($route->getName(), 'purchases.debit-notes')) {
            return;
        }

        // Use a unique name in the request container to prevent double execution
        if (app()->has('debit_note_fields_pushed')) {
            return;
        }

        $document = $view_data['document'] ?? null;
        $contact = $view_data['contact'] ?? null;

        $bills = [];
        if ($contact instanceof Contact) {
            $bills = $contact->bills()
                ->whereIn('status', ['received', 'partial', 'paid'])
                ->pluck('document_number', 'id')
                ->toArray();
        }

        app()->instance('debit_note_fields_pushed', true);

        // Push to order_number_start stack which exists in metadata.blade.php
        $view->getFactory()->startPush(
            'order_number_start',
            view('partials.documents.debit_note.bill_selection', [
                'reference_bills' => $bills,
            ])->render()
        );
    }
}
