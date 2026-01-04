<?php

namespace App\Http\ViewComposers\Document;

use Illuminate\View\View;
use App\Services\Credits;

class UseCredits
{
    /**
     * @var Credits
     */
    private $credits;

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
        $applied_credits = $this->credits->getAppliedCredits($invoice);
        $available_credits = $this->credits->getAvailableCredits($invoice->contact_id);

        if((empty($invoice->paid) || ($invoice->paid + $applied_credits != $invoice->amount)) && $available_credits) {
            $view->getFactory()->startPush(
                'timeline_get_paid_body_button_payment_end',
                view('partials.documents.invoice.use_credits_button')
            );
        }

        $view->getFactory()->startPush(
            'body_end',
            '<div id="credit-debit-notes-vue-entrypoint"><component v-bind:is="component"></component></div>'
        );
    }
}
