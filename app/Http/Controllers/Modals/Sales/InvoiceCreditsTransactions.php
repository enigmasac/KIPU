<?php

namespace App\Http\Controllers\Modals\Sales;

use App\Abstracts\Http\Controller;
use App\Models\Document\Document;
use App\Http\Requests\Document\CreditsTransaction as Request;
use App\Models\Setting\Currency;
use Illuminate\Http\JsonResponse;
use App\Jobs\Document\Credits\CreateCreditsTransaction;
use App\Services\Credits;

class InvoiceCreditsTransactions extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Add CRUD permission check
        $this->middleware('permission:create-sales-credit-notes')->only('create', 'store');
    }

    public function create(Document $invoice, Credits $credits): JsonResponse
    {
        $currency = Currency::where('code', $invoice->currency_code)->first();

        $paid = $invoice->paid;

        $grand_total = $invoice->amount - $paid;

        $applied_credits = $credits->getAppliedCredits($invoice);
        if (!empty($applied_credits)) {
            $grand_total = $grand_total - $applied_credits;
        }

        $available_credits = max($credits->getAvailableCredits($invoice->contact_id), 0);

        $invoice->grand_total = round(min($available_credits, $grand_total), $currency->precision);

        $route = route('modals.sales.invoices.invoice.credits-transactions.store', $invoice->id);

        $html = view('modals.sales.invoices.credit', compact('invoice', 'currency', 'route'))->render();

        return response()->json([
            'success' => true,
            'error' => false,
            'message' => 'null',
            'html' => $html,
            'data' => [
                'title' => trans('credit_notes.use_credits')." (" . money($available_credits, $currency->code, true)->format() . " available)",
                'buttons' => [
                    'cancel' => [
                        'text' => trans('general.cancel'),
                        'class' => 'btn-outline-secondary'
                    ],
                    'confirm' => [
                        'text' => trans('general.save'),
                        'class' => 'btn-success'
                    ]
                ]
            ]
        ]);
    }

    public function store(Document $invoice, Request $request): JsonResponse
    {
        $response = $this->ajaxDispatch(new CreateCreditsTransaction($invoice, $request));

        if ($response['success']) {
            $response['redirect'] = route('sales.invoices.show', $invoice->id);

            $message = trans('credit_notes.credits_used');

            flash($message)->success();
        } else {
            $response['redirect'] = null;
        }

        return response()->json($response);
    }
}
