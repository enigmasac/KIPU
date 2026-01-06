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
    public function latest_sunat_emission()
    {
        return $this->hasOne(\Modules\Sunat\Models\Emission::class, 'document_id')->orderBy('created_at', 'desc');
    }

    /**
     * Obtener el monto en letras para SUNAT.
     */
    public function getAmountInWordsAttribute()
    {
        $amount = (float) $this->amount;
        $formatter = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);

        $entire = floor($amount);
        $fraction = round(($amount - $entire) * 100);

        $currency = $this->currency_code === 'PEN' ? 'SOLES' : ($this->currency_code === 'USD' ? 'DOLARES' : $this->currency_code);

        $words = strtoupper($formatter->format($entire));

        return "SON {$words} CON " . str_pad($fraction, 2, '0', STR_PAD_LEFT) . "/100 {$currency}";
    }

    /**
     * Obtener el contenido del QR para SUNAT.
     */
    public function getSunatQrContentAttribute()
    {
        $emission = $this->latest_sunat_emission;
        $hash = $emission ? $emission->hash : '';

        $ruc_emisor = setting('company.tax_number', '');
        $tipo_doc = str_starts_with($this->document_number, 'F') ? '01' : '03'; // Factura o Boleta

        if (strpos($this->document_number, '-') !== false) {
            [$serie, $correlativo] = explode('-', $this->document_number);
        } else {
            $serie = substr($this->document_number, 0, 4);
            $correlativo = substr($this->document_number, 4);
        }

        $igv = 0;
        foreach ($this->totals as $total) {
            if (str_contains(strtolower($total->title), 'igv') || str_contains(strtolower($total->title), 'impuesto')) {
                $igv += $total->amount;
            }
        }

        $total = $this->amount;
        $fecha = \Carbon\Carbon::parse($this->issued_at)->format('Y-m-d');

        $tipo_doc_receptor = strlen($this->contact_tax_number) === 11 ? '6' : (strlen($this->contact_tax_number) === 8 ? '1' : '0');
        $num_doc_receptor = $this->contact_tax_number;

        return "{$ruc_emisor}|{$tipo_doc}|{$serie}|{$correlativo}|{$igv}|{$total}|{$fecha}|{$tipo_doc_receptor}|{$num_doc_receptor}|{$hash}|";
    }

    /**
     * Obtener el QR como imagen base64 para PDF.
     */
    public function getSunatQrImageAttribute()
    {
        try {
            $content = $this->sunat_qr_content;

            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $svg = $writer->writeString($content);

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Exception $e) {
            return '';
        }
    }
}