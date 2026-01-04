<?php

namespace App\Http\ViewComposers\Document;

use App\Traits\DateTime;
use Illuminate\View\View;
use App\Services\Credits;

class ShowCreditsTransactions
{
    use DateTime;

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
        $transactions = $this->credits->getTransactions($invoice->id);

        if ($transactions->isEmpty()) {
            return;
        }

        $view->getFactory()->startPush(
            'row_footer_transactions_end',
            view(
                'partials.documents.invoice.credits_transactions',
                [
                    'invoice' => $invoice,
                    'date_format' => $this->getCompanyDateFormat(),
                    'transactions' => $transactions
                ]
            )
        );
    }
}
