<?php

namespace Modules\PeruCore\Observers;

use App\Models\Common\Contact;
use Illuminate\Validation\ValidationException;

class ContactObserver
{
    /**
     * Handle the Contact "saving" event.
     *
     * @param  \App\Models\Common\Contact  $contact
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saving(Contact $contact)
    {
        // Only validate if tax_number is present
        if (empty($contact->tax_number)) {
            return;
        }

        // Validate based on document_type
        // Note: We need to agree on the values for document_type.
        // SUNAT Catalog 06:
        // 1 = DNI
        // 6 = RUC
        // 4 = Carnet Extranjeria
        // 7 = Pasaporte

        // If document_type is not set, we might infer it or skip.
        // Let's assume the UI sends the code (1, 6, etc.) or a descriptive string.
        // For now, I'll implement flexible detection if document_type is missing.

        $type = $contact->document_type;
        $number = $contact->tax_number;

        // Auto-detect type if missing based on length (Common helper logic)
        if (empty($type)) {
            if (strlen($number) === 11) $type = '6'; // RUC
            elseif (strlen($number) === 8) $type = '1'; // DNI
        }

        if ($type == '6') { // RUC
            if (strlen($number) !== 11 || !is_numeric($number)) {
                 throw ValidationException::withMessages([
                    'tax_number' => trans('peru-core::general.errors.invalid_ruc_format'),
                ]);
            }
            // Ideally implement Modulo 11 check here for RUC
        } elseif ($type == '1') { // DNI
            if (strlen($number) !== 8 || !is_numeric($number)) {
                throw ValidationException::withMessages([
                    'tax_number' => trans('peru-core::general.errors.invalid_dni_format'),
                ]);
            }
        }
    }
}
