<?php

namespace Modules\Sunat\Services;

use App\Models\Document\Document;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\DocumentInterface;
use Illuminate\Support\Facades\Crypt;
use Modules\Sunat\Models\Certificate;
use Modules\Sunat\Services\Builders\InvoiceBuilder;
use Modules\Sunat\Services\Builders\CreditNoteBuilder;
use Modules\Sunat\Services\Builders\DebitNoteBuilder;

class GreenterService
{
    private See $see;
    private ?string $lastXml = null;

    public function __construct()
    {
        $this->see = new See();
    }

    public function configure(int $companyId): self
    {
        $certificate = Certificate::forCompany($companyId)->active()->firstOrFail();
        $this->see->setCertificate($certificate->getDecryptedContent());

        $environment = setting('sunat.environment', 'beta');
        $endpoint = $environment === 'production'
            ? SunatEndpoints::FE_PRODUCCION
            : SunatEndpoints::FE_BETA;
        $this->see->setService($endpoint);

        $ruc = setting('sunat.ruc') ?: '20000000001';
        $solUser = setting('sunat.sol_user') ?: 'MODDATOS';

        // Decrypt the SOL password if it's encrypted
        $solPasswordEncrypted = setting('sunat.sol_password');
        if ($solPasswordEncrypted) {
            try {
                $solPassword = Crypt::decryptString($solPasswordEncrypted);
            } catch (\Exception $e) {
                // If decryption fails, assume it's plain text (beta mode default)
                $solPassword = $solPasswordEncrypted;
            }
        } else {
            $solPassword = 'moddatos'; // Default beta password
        }

        $this->see->setClaveSOL($ruc, $solUser, $solPassword);

        return $this;
    }

    public function emitInvoice(Document $document): array
    {
        $builder = new InvoiceBuilder();
        $invoice = $builder->build($document);
        return $this->emit($invoice);
    }

    public function emitCreditNote(Document $document): array
    {
        $builder = new CreditNoteBuilder();
        $note = $builder->build($document);
        return $this->emit($note);
    }

    public function emitDebitNote(Document $document): array
    {
        $builder = new DebitNoteBuilder();
        $note = $builder->build($document);
        return $this->emit($note);
    }

    protected function emit(DocumentInterface $document): array
    {
        try {
            $result = $this->see->send($document);
            $this->lastXml = $this->see->getFactory()->getLastXml();

            if (!$result->isSuccess()) {
                $error = $result->getError();
                return [
                    'success' => false,
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage(),
                    'xml' => $this->lastXml,
                ];
            }

            $cdr = $result->getCdrResponse();
            return [
                'success' => true,
                'code' => (string) $cdr->getCode(),
                'message' => $cdr->getDescription(),
                'notes' => $cdr->getNotes() ?? [],
                'xml' => $this->lastXml,
                'cdr' => $result->getCdrZip(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    public function processEmission(Document $document): void
    {
        // Refresh document to get latest data (e.g. updated issued_at)
        $document->refresh();

        $companyId = $document->company_id;

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

        // Check if already emitted (DB Record or Document Status)
        if ($document->sunat_status === 'accepted') {
            return;
        }

        $existing = \Modules\Sunat\Models\Emission::where('document_id', $document->id)
            ->where('status', \Modules\Sunat\Models\Emission::STATUS_ACCEPTED)
            ->first();

        if ($existing) {
            return;
        }

        try {
            $this->configure($companyId);

            $result = match ($type) {
                'invoice' => $this->emitInvoice($document),
                'credit_note' => $this->emitCreditNote($document),
                'debit_note' => $this->emitDebitNote($document),
            };

            // Create emission record
            $emission = \Modules\Sunat\Models\Emission::create([
                'company_id' => $companyId,
                'document_id' => $document->id,
                'document_type' => $type,
                'document_number' => $document->document_number,
                'status' => \Modules\Sunat\Models\Emission::STATUS_PENDING,
            ]);

            if ($result['success']) {
                // Store XML
                if (!empty($result['xml'])) {
                    $xmlPath = "sunat/{$companyId}/xml/{$document->document_number}.xml";
                    \Illuminate\Support\Facades\Storage::put($xmlPath, $result['xml']);
                    $emission->xml_path = $xmlPath;
                }

                // Store CDR
                if (!empty($result['cdr'])) {
                    $cdrPath = "sunat/{$companyId}/cdr/R-{$document->document_number}.zip";
                    \Illuminate\Support\Facades\Storage::put($cdrPath, $result['cdr']);
                    $emission->cdr_path = $cdrPath;
                }

                $emission->markAsAccepted($result['code'], $result['message'], $result['hash'] ?? null);
                $document->update(['sunat_status' => 'accepted']);

                \Illuminate\Support\Facades\Log::info("SUNAT: Document {$document->document_number} emitted successfully");
            } else {
                $emission->markAsError($result['error_message'] ?? 'Error desconocido');
                $document->update([
                    'sunat_status' => 'rechazado',
                    'sunat_message' => $result['error_message'] ?? 'Error desconocido',
                    'sunat_code' => $result['error_code'] ?? null,
                ]);

                // If it's a rejected credit note, revert invoice status if necessary
                if ($type === 'credit_note' && $document->invoice_id) {
                    $invoice = $document->invoice;
                    if ($invoice) {
                        // Force recalculate paid status
                        $paid = $invoice->paid;
                        if ($paid < $invoice->amount && in_array($invoice->status, ['paid', 'cancelled'])) {
                            $invoice->update(['status' => 'sent']);
                        }
                    }
                }
                \Illuminate\Support\Facades\Log::error("SUNAT: Failed to emit {$document->document_number}: " . ($result['error_message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SUNAT: Exception emitting {$document->document_number}: " . $e->getMessage());
        }
    }

    public function getLastXml(): ?string
    {
        return $this->lastXml;
    }
}
