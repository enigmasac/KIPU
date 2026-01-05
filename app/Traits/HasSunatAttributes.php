<?php

namespace App\Traits;

use App\Models\Document\Document;
use Illuminate\Support\Facades\DB;

trait HasSunatAttributes
{
    /**
     * Obtener el número de factura afectada (para Notas de Crédito/Débito).
     */
    public function getInvoiceNumberAttribute()
    {
        if ($this->relationLoaded('referenced_document') && $this->referenced_document) {
            return $this->referenced_document->document_number;
        }

        $id = $this->invoice_id ?: $this->parent_id;

        if ($id) {
            return DB::table('documents')->where('id', $id)->value('document_number');
        }

        return null;
    }

    /**
     * Obtener la descripción del motivo SUNAT para notas.
     */
    public function getReasonDescriptionAttribute()
    {
        $code = $this->credit_note_reason_code ?? $this->debit_note_reason_code;

        if (!$code) {
            return null;
        }

        $reasons = [
            '01' => 'Anulación de la operación',
            '02' => 'Anulación por error en el RUC',
            '03' => 'Corrección por error en la descripción',
            '04' => 'Descuento global',
            '05' => 'Descuento por ítem',
            '06' => 'Devolución total',
            '07' => 'Devolución por ítem',
            '08' => 'Bonificación',
            '09' => 'Disminución en el valor',
            '10' => 'Otros Conceptos',
        ];

        return ($reasons[$code] ?? $code);
    }
}