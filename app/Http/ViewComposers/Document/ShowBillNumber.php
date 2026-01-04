<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\Support\Str;
use Illuminate\View\View;

class ShowBillNumber
{
    public function compose(View $view)
    {
        $debit_note = $view->getData()['debit_note'];

        if(!$debit_note->bill_number) {
            return;
        }

        $print = Str::contains($view->name(), 'print');

        $bill_route = $this->getBillRoute($view, $debit_note);

        $view->getFactory()->startPush(
            'issued_at_input_end',
            view('partials.documents.debit_note.bill_number', compact('debit_note', 'print', 'bill_route'))
        );
    }

    private function getBillRoute(View $view, $debit_note): string
    {
        if (isset($view->getData()['bill_signed_url'])) {
            return $view->getData()['bill_signed_url'];
        }

        if (Str::contains($view->name(), 'portal')) {
            return route('portal.bills.show', ['bill' => $debit_note->bill_id]);
        }

        return route('bills.show', ['bill' => $debit_note->bill_id]);
    }

}
