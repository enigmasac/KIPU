<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\View\View;
use App\Models\Document\CreditNote;
use App\Models\Document\Document as DocumentModel;
use App\Models\Common\Contact;

class AddInvoiceSelectionField
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        $view_data = $view->getData();

        $type = $view_data['type'] ?? null;

        $supported_types = [
            CreditNote::TYPE,
            DocumentModel::DEBIT_NOTE_TYPE,
        ];

        if (! in_array($type, $supported_types)) {
            return;
        }

        if (app()->has('document_reason_fields_pushed')) {
            return;
        }

        $document = $view_data['document'] ?? null;
        $contact = $view_data['contact'] ?? null;

        if (! $contact && request()->has('invoice_id')) {
            $invoice = DocumentModel::find(request('invoice_id'));
            if ($invoice) {
                $contact = $invoice->contact;
            }
        }

        $invoices = [];
        if ($contact instanceof Contact) {
            $invoices = $contact->invoices()
                ->whereIn('status', ['sent', 'partial', 'paid'])
                ->pluck('document_number', 'id')
                ->toArray();
        }

        $pre_selected_invoice_id = old('invoice_id', old('parent_id', request('invoice_id', request('invoice', ($document->invoice_id ?? null)))));
        $selected_invoice = null;
        if ($pre_selected_invoice_id) {
            $selected_invoice = DocumentModel::invoice()->find($pre_selected_invoice_id);
        }

        $reasons = $type === CreditNote::TYPE ? [
            '01' => '01 - Anulación de la operación',
            '02' => '02 - Anulación por error en el RUC',
            '03' => '03 - Corrección por error en la descripción',
            '04' => '04 - Descuento global',
            '05' => '05 - Descuento por ítem',
            '06' => '06 - Devolución total',
            '07' => '07 - Devolución por ítem',
            '08' => '08 - Bonificación',
            '09' => '09 - Disminución en el valor',
            '10' => '10 - Otros Conceptos',
        ] : [
            '01' => '01 - Intereses por mora',
            '02' => '02 - Variación en el valor',
            '03' => '03 - Cobros adicionales',
            '04' => '04 - Gastos asociados',
            '05' => '05 - Penalidades',
            '06' => '06 - Otros conceptos',
        ];

        $view->getFactory()->startPush(
            'order_number_start',
            view($type === CreditNote::TYPE
                ? 'partials.documents.credit_note.invoice_selection'
                : 'partials.documents.debit_note.invoice_selection', [
                'reference_invoices' => $invoices,
                'sunat_reasons' => $reasons,
                'document' => $document,
                'pre_selected_invoice_id' => $pre_selected_invoice_id,
                'selected_invoice' => $selected_invoice,
            ])->render()
        );

        app()->instance('document_reason_fields_pushed', true);
    }
}
