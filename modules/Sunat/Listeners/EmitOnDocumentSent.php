<?php

namespace Modules\Sunat\Listeners;

use App\Events\Document\DocumentMarkedSent;
use Modules\Sunat\Models\Emission;
use Modules\Sunat\Services\GreenterService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmitOnDocumentSent
{
    public function handle(DocumentMarkedSent $event): void
    {
        $document = $event->document;
        $companyId = $document->company_id;

        // Check if auto-emit is enabled
        if (!setting('sunat.auto_emit', false)) {
            return;
        }

        // Only process invoices, credit notes, and debit notes
        $type = match ($document->type) {
            'invoice' => 'invoice',
            'credit-note' => 'credit_note',
            'debit-note' => 'debit_note',
            default => null,
        };

        if (!$type) {
            return;
        }

        try {
            $greenterService = app(GreenterService::class);
            $greenterService->processEmission($document);
        } catch (\Exception $e) {
            Log::error("SUNAT: Exception emitting {$document->document_number}: " . $e->getMessage());
        }
    }
}
