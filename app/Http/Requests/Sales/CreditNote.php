<?php

namespace App\Http\Requests\Sales;

use App\Http\Requests\Document\Document;

class CreditNote extends Document
{
    protected function prepareForValidation()
    {
        $this->merge([
            'type' => 'credit-note',
            'sunat_document_type' => '07',
        ]);

        $invoice_id = $this->input('invoice_id');
        if (is_array($invoice_id)) {
            $invoice_id = $invoice_id['id'] ?? $invoice_id['value'] ?? $invoice_id['invoice_id'] ?? null;
            $this->merge(['invoice_id' => $invoice_id]);
        }

        $parent_id = $this->input('parent_id');
        if (is_array($parent_id)) {
            $parent_id = $parent_id['id'] ?? $parent_id['value'] ?? $parent_id['invoice_id'] ?? null;
            $this->merge(['parent_id' => $parent_id]);
        }

        if (! $this->filled('parent_id') && $this->filled('invoice_id')) {
            $this->merge(['parent_id' => $this->input('invoice_id')]);
        }

        if (! $this->filled('invoice_id') && $this->filled('parent_id')) {
            $this->merge(['invoice_id' => $this->input('parent_id')]);
        }

        if ($this->has('credit_note_reason_code')) {
            $reason = $this->input('credit_note_reason_code');

            if (is_array($reason)) {
                $reason = $reason['id'] ?? $reason['value'] ?? $reason['code'] ?? $reason['key'] ?? $reason['name'] ?? '';
            }

            if (is_string($reason)) {
                $reason = trim($reason);
                if ($reason !== '' && preg_match('/\d+/', $reason, $matches)) {
                    $reason = $matches[0];
                }
            }

            if (is_numeric($reason)) {
                $reason = str_pad((string) $reason, 2, '0', STR_PAD_LEFT);
            }

            $this->merge(['credit_note_reason_code' => (string) $reason]);
        }
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['issued_at'] = str_replace('|before_or_equal:due_at', '', $rules['issued_at']);
        $rules['due_at'] = str_replace('|after_or_equal:issued_at', '', $rules['due_at']);

        // SUNAT: referencia obligatoria y motivo controlado
        $rules['parent_id'] = 'required|integer|exists:documents,id';
        $rules['invoice_id'] = 'required|integer|exists:documents,id';
        $rules['credit_note_reason_code'] = 'required|string|in:01,02,03,04,05,06,07,08,09,10';

        return $rules;
    }
}
