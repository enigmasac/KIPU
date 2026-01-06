<?php

namespace Modules\PeruCore\Observers;

use App\Models\Document\Document;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     *
     * @param  \App\Models\Document\Document  $document
     * @return void
     */
    public function created(Document $document)
    {
        // Solo procesamos documentos de venta relevantes para SUNAT
        if (!in_array($document->type, [Document::INVOICE_TYPE, Document::CREDIT_NOTE_TYPE, Document::DEBIT_NOTE_TYPE])) {
            return;
        }

        \Log::info("PeruCore Observer: Procesando documento {$document->document_number} (Tipo: {$document->type})");

        // Aquí se implementará la lógica de:
        // 1. Validación de reglas SUNAT (700 soles, RUC/DNI coincidencia)
        // 2. Preparación de datos para Greenter
        // 3. Generación de XML local (en modo offline por ahora)
    }

    /**
     * Handle the Document "updating" event.
     *
     * @param  \App\Models\Document\Document  $document
     * @return void
     */
    public function updating(Document $document)
    {
        // Lógica para proteger documentos ya emitidos
    }
}
