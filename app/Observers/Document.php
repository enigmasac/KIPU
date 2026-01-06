<?php

namespace App\Observers;

use App\Abstracts\Observer;
use App\Traits\Jobs;
use App\Jobs\Document\Credits\DeleteCreditNoteCreditsTransactions;
use App\Models\Document\CreditNote;
use App\Models\Document\DebitNote;
use App\Models\Document\CreditsTransaction;

class Document extends Observer
{
    use Jobs;

    /**
     * @param \App\Models\Document\Document $document
     */
    public function created($document)
    {
        if ($document->type !== CreditNote::TYPE) {
            return;
        }

        $parent_id = $document->parent_id ?: $document->invoice_id;

        if (empty($parent_id)) {
            return;
        }

        if (empty($document->parent_id) && !empty($document->invoice_id)) {
            \App\Models\Document\Document::withoutEvents(function () use ($document, $parent_id) {
                $document->parent_id = $parent_id;
                $document->save();
            });
        }

        $is_rejected = $this->isSunatRejected($document);

        if (!$is_rejected && $document->amount > 0) {
            // Create the transaction to reduce the parent's balance
            CreditsTransaction::create([
                'company_id' => $document->company_id,
                'type' => 'expense',
                'paid_at' => $document->issued_at,
                'amount' => $document->amount,
                'currency_code' => $document->currency_code,
                'currency_rate' => $document->currency_rate,
                'document_id' => $parent_id,
                'contact_id' => $document->contact_id,
                'category_id' => $document->category_id,
                'description' => trans('general.credit_note_applied', ['number' => $document->document_number]),
            ]);
        }

        // Check Parent Balance
        $parent = \App\Models\Document\Document::find($parent_id);
        if (!$parent) {
            return;
        }

        $parent->refresh();

        $precision = currency($parent->currency_code)->getPrecision();
        $credit_notes_total = $this->getApprovedCreditNotesTotal($parent);

        // Only cancel if credit_notes_total >= invoice amount AND the credit note is approved by SUNAT
        // We require explicit SUNAT acceptance before cancelling the invoice
        $cn_is_approved = strtolower((string) $document->sunat_status) === 'accepted';

        if (bccomp((string) $credit_notes_total, (string) $parent->amount, $precision) >= 0) {
            // Only cancel if the credit note is already approved by SUNAT
            if ($cn_is_approved) {
                if ($parent->status !== 'cancelled') {
                    $parent->status = 'cancelled';
                    $parent->save();
                }
            }
        }
    }

    /**
     * @param \App\Models\Document\Document $document
     */
    public function deleted($document)
    {
        if ($document->type === CreditNote::TYPE) {
            $this->dispatch(new DeleteCreditNoteCreditsTransactions($document));
        }

        if ($document->type === \App\Models\Document\Document::INVOICE_TYPE) {
            \App\Models\Document\CreditsTransaction::expense()->document($document->id)->delete();
        }
    }

    /**
     * @param \App\Models\Document\Document $document
     */
    public function updated($document)
    {
        if ($document->type !== CreditNote::TYPE) {
            return;
        }

        $parent_id = $document->parent_id ?: $document->invoice_id;

        if (empty($parent_id)) {
            return;
        }

        $parent = \App\Models\Document\Document::find($parent_id);
        if (!$parent) {
            return;
        }

        $is_rejected = $this->isSunatRejected($document);

        $description = trans('general.credit_note_applied', ['number' => $document->document_number]);
        $credit_transaction = CreditsTransaction::where('document_id', $parent_id)
            ->where('type', 'expense')
            ->where('description', $description)
            ->latest()
            ->first();

        if ($is_rejected) {
            if ($credit_transaction) {
                CreditsTransaction::withoutEvents(function () use ($credit_transaction) {
                    $credit_transaction->delete();
                });
            }
        } elseif (!$credit_transaction) {
            CreditsTransaction::create([
                'company_id' => $document->company_id,
                'type' => 'expense',
                'paid_at' => $document->issued_at,
                'amount' => $document->amount,
                'currency_code' => $document->currency_code,
                'currency_rate' => $document->currency_rate,
                'document_id' => $parent_id,
                'contact_id' => $document->contact_id,
                'category_id' => $document->category_id,
                'description' => $description,
            ]);
        } else {
            $credit_transaction->amount = $document->amount;
            $credit_transaction->currency_code = $document->currency_code;
            $credit_transaction->currency_rate = $document->currency_rate;
            $credit_transaction->paid_at = $document->issued_at;
            $credit_transaction->save();
        }

        $precision = currency($parent->currency_code)->getPrecision();
        $credit_notes_total = $this->getApprovedCreditNotesTotal($parent);

        // Only cancel if credit_notes_total >= invoice amount AND the credit note is approved by SUNAT
        // We require explicit SUNAT acceptance before cancelling the invoice
        $cn_is_approved = strtolower((string) $document->sunat_status) === 'accepted';

        if (bccomp((string) $credit_notes_total, (string) $parent->amount, $precision) >= 0) {
            // Only cancel if the credit note is already approved by SUNAT
            if ($cn_is_approved) {
                if ($parent->status !== 'cancelled') {
                    $parent->status = 'cancelled';
                    $parent->save();
                }
            }
        } elseif ($parent->status === 'cancelled') {
            $parent->status = $parent->transactions->count() > 0 ? 'partial' : 'sent';
            $parent->save();
        }
    }

    protected function getApprovedCreditNotesTotal($parent): float
    {
        return \App\Models\Document\Document::where('invoice_id', $parent->id)
            ->where('type', CreditNote::TYPE)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->whereNull('sunat_status')
                    ->orWhere('sunat_status', '!=', 'rechazado');
            })
            ->sum('amount');
    }

    protected function isSunatRejected($document): bool
    {
        return strtolower((string) $document->sunat_status) === 'rechazado';
    }
}
