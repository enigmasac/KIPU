<?php

namespace App\Jobs\Document\Credits;

use App\Abstracts\Job;
use App\Interfaces\Job\ShouldCreate;
use App\Jobs\Document\CreateDocumentHistory;
use App\Models\Document\Document;
use App\Traits\Currencies;
use App\Utilities\Date;
use Exception;
use App\Models\Document\CreditNote;
use App\Models\Document\CreditsTransaction;
use App\Services\Credits;

class CreateCreditsTransaction extends Job implements ShouldCreate
{
    use Currencies;

    protected Document $document;

    public function __construct($document, $request = [])
    {
        $this->document = $document;

        parent::__construct($request);
    }

    /**
     * @throws \Throwable
     */
    public function handle(): CreditsTransaction
    {
        $this->prepareRequest();

        if ($this->document->type === Document::INVOICE_TYPE) {
            $this->checkAmount();
        }

        \DB::transaction(function () {
            $this->model = CreditsTransaction::create($this->request->all());

            $this->document->save();

            if ($this->document->type === Document::INVOICE_TYPE) {
                $this->createInvoiceHistory();
            } else {
                $this->createCreditNoteHistory();
            }
        });

        return $this->model;
    }

    protected function prepareRequest()
    {
        if (!isset($this->request['amount'])) {
            $this->request['amount'] = $this->document->amount;

            if ($this->document->type === Document::INVOICE_TYPE) {
                $this->request['amount'] -= $this->document->paid;
            }
        }

        if (!isset($this->request['paid_at'])) {
            $this->request['paid_at'] = ($this->document->type === CreditNote::TYPE) ? $this->document->issued_at : Date::now()->format('Y-m-d');
        }

        $this->request['company_id'] = company_id();
        $this->request['type'] = ($this->document->type === Document::INVOICE_TYPE) ? 'expense' : 'income';
        $this->request['currency_code'] = isset($this->request['currency_code']) ? $this->request['currency_code'] : $this->document->currency_code;
        $this->request['currency_rate'] = currency($this->request['currency_code'])->getRate();
        $this->request['document_id'] = isset($this->request['document_id']) ? $this->request['document_id'] : $this->document->id;
        $this->request['contact_id'] = isset($this->request['contact_id']) ? $this->request['contact_id'] : $this->document->contact_id;
        $this->request['category_id'] = isset($this->request['category_id']) ? $this->request['category_id'] : $this->document->category_id;
        $this->request['notify'] = isset($this->request['notify']) ? $this->request['notify'] : 0;
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function checkAmount()
    {
        $code = $this->request['currency_code'];
        $rate = $this->request['currency_rate'];
        $precision = currency($code)->getPrecision();

        $amount = $this->request['amount'] = round($this->request['amount'], $precision);

        if ($this->document->currency_code != $code) {
            $converted_amount = $this->convertBetween($amount, $code, $rate, $this->document->currency_code, $this->document->currency_rate);

            $amount = round($converted_amount, $precision);
        }

        $credits = new Credits();
        $available_credits = $credits->getAvailableCredits($this->document->contact_id);
        if ($amount > $available_credits) {
            $message = trans('credit_notes.messages.error.not_enough_credits', ['credits' => money($available_credits, $code, true)]);

            throw new Exception($message);
        }

        $applied_credits = $credits->getAppliedCredits($this->document);

        $total_amount = round($this->document->amount - $this->document->paid - $applied_credits, $precision);
        unset($this->document->reconciled);

        $compare = bccomp($amount, $total_amount, $precision);

        if ($compare === 1) {
            $error_amount = $total_amount;

            if ($this->document->currency_code != $code) {
                $converted_amount = $this->convertBetween($total_amount, $this->document->currency_code, $this->document->currency_rate, $code, $rate);

                $error_amount = round($converted_amount, $precision);
            }

            $message = trans('credit_notes.messages.error.over_payment', ['amount' => money($error_amount, $code, true)]);

            throw new Exception($message);
        } else {
            $this->document->status = ($compare === 0) ? 'paid' : 'partial';
        }

        return true;
    }

    protected function createInvoiceHistory(): void
    {
        $history_desc = money((double) $this->model->amount, (string) $this->model->currency_code, true)->format() . ' ' . trans_choice('general.credit_notes', 1);

        $this->dispatch(new CreateDocumentHistory($this->document, 0, $history_desc));
    }

    protected function createCreditNoteHistory(): void
    {
        $amount = money((double) $this->model->amount, (string) $this->model->currency_code, true)->format();
        $history_desc =  trans('credit_notes.customer_credited_with', ['customer' => $this->document->contact_name, 'amount' => $amount]);

        $this->dispatch(new CreateDocumentHistory($this->document, 0, $history_desc));
    }
}
