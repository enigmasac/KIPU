<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\Support\Str;
use Illuminate\View\View;

class ShowInvoiceNumber
{
    public function compose(View $view)
    {
        $credit_note = $view->getData()['credit_note'];

        $invoice_number = $credit_note->invoice_number;

        if (!$invoice_number) {
            return;
        }

        $print = Str::contains($view->name(), 'print');

        $invoice_route = $this->getInvoiceRoute($view, $credit_note);

        $reasons = [
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
        ];

        $reason_description = $reasons[$credit_note->reason_code] ?? $credit_note->reason_code;

        $view->getFactory()->startPush(
            'issued_at_input_end',
            view('partials.documents.credit_note.invoice_number', compact('credit_note', 'print', 'invoice_route', 'reason_description'))
        );
    }

    private function getInvoiceRoute(View $view, $credit_note): string
    {
        if (isset($view->getData()['invoice_signed_url'])) {
            return $view->getData()['invoice_signed_url'];
        }

        if (Str::contains($view->name(), 'portal')) {
            return route('portal.invoices.show', ['invoice' => $credit_note->invoice_id]);
        }

        return route('invoices.show', ['invoice' => $credit_note->invoice_id]);
    }

}