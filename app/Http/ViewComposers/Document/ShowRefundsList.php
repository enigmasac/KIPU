<?php

namespace App\Http\ViewComposers\Document;

use App\Models\Banking\Transaction;
use Illuminate\View\View;
use App\Models\Document\CreditNote;
use App\Models\Document\DebitNote;

class ShowRefundsList
{
    public function compose(View $view): void
    {
        $view_data = $view->getData();

        if (empty($view_data['type']) || !in_array($view_data['type'], ['credit-note', 'debit-note'])) {
            return;
        }

        $document = $view_data['document'] ?? $view_data['credit_note'] ?? $view_data['debit_note'] ?? null;

        if (!$document) {
            return;
        }

        if ($document->credit_customer_account) {
            return;
        }

        if ($document->status !== 'sent') {
            return;
        }

        $refunds = Transaction::documentId($document->id)->get();

        // TODO: adjust for a case with different currencies in transactions
        $amount_available = $document->amount - $refunds->sum('amount');

        $view_data['description'] = trans('general.amount_available').': '.
            '<span class="font-medium">'.money($amount_available, $document->currency_code, true).'</span>';
        $view_data['amount_available'] = $amount_available;

        if ($document->type === CreditNote::TYPE) {
            $view_data['accordion_title'] = trans('credit_notes.refund_customer');
            $view_data['button_text'] = trans('credit_notes.make_refund');
            $view_data['list_title'] = trans('credit_notes.refunds_made');
            $view_data['refund_translation'] = 'credit_notes.refund_transaction';
        } else {
            $view_data['accordion_title'] = trans('debit_notes.receive_refund');
            $view_data['button_text'] = trans('debit_notes.receive_refund');
            $view_data['list_title'] = trans('debit_notes.refunds_received');
            $view_data['refund_translation'] = 'debit_notes.refund_transaction';
        }

        $view->getFactory()->startPush(
            'get_paid_start',
            view('partials.documents.refunds_list', $view_data)
        );
    }
}
