<?php

namespace Modules\Sunat\Http\Controllers;

use App\Abstracts\Http\Controller;
use App\Models\Document\Document;
use Modules\Sunat\Models\Emission;
use Modules\Sunat\Services\GreenterService;
use Illuminate\Support\Facades\Storage;

class EmitController extends Controller
{
    public function invoice(Document $document, GreenterService $greenterService)
    {
        return $this->emit($document, $greenterService, 'invoice');
    }

    public function creditNote(Document $document, GreenterService $greenterService)
    {
        return $this->emit($document, $greenterService, 'credit_note');
    }

    public function debitNote(Document $document, GreenterService $greenterService)
    {
        return $this->emit($document, $greenterService, 'debit_note');
    }

    protected function emit(Document $document, GreenterService $greenterService, string $type)
    {
        // Verificar si ya fue emitido
        $existing = Emission::where('document_id', $document->id)
            ->where('status', Emission::STATUS_ACCEPTED)
            ->first();

        if ($existing) {
            flash('El documento ya fue emitido a SUNAT')->warning();
            return back();
        }

        try {
            $greenterService->configure($document->company_id);

            $result = match ($type) {
                'invoice' => $greenterService->emitInvoice($document),
                'credit_note' => $greenterService->emitCreditNote($document),
                'debit_note' => $greenterService->emitDebitNote($document),
            };

            // Crear registro de emisión
            $emission = Emission::create([
                'company_id' => $document->company_id,
                'document_id' => $document->id,
                'document_type' => $type,
                'document_number' => $document->document_number,
                'status' => Emission::STATUS_PENDING,
            ]);

            if ($result['success']) {
                // Guardar XML
                if (!empty($result['xml'])) {
                    $xmlPath = "sunat/{$document->company_id}/xml/{$document->document_number}.xml";
                    Storage::put($xmlPath, $result['xml']);
                    $emission->xml_path = $xmlPath;
                }

                // Guardar CDR
                if (!empty($result['cdr'])) {
                    $cdrPath = "sunat/{$document->company_id}/cdr/R-{$document->document_number}.zip";
                    Storage::put($cdrPath, $result['cdr']);
                    $emission->cdr_path = $cdrPath;
                }

                $emission->markAsAccepted($result['code'], $result['message'], $result['hash'] ?? null);
                $document->update(['sunat_status' => 'accepted']);

                flash("Documento emitido correctamente: {$result['message']}")->success();
            } else {
                $emission->markAsError($result['error_message'] ?? 'Error desconocido');
                $document->update([
                    'sunat_status' => 'rechazado',
                    'sunat_message' => $result['error_message'] ?? 'Error desconocido',
                    'sunat_code' => $result['error_code'] ?? null,
                ]);
                flash("Error al emitir: {$result['error_message']}")->error();
            }

        } catch (\Exception $e) {
            flash("Error: {$e->getMessage()}")->error();
        }

        return back();
    }
}
