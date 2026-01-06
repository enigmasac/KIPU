<?php

namespace Modules\Sunat\Services\Builders;

use App\Models\Document\Document;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;

class CreditNoteBuilder
{
    public function build(Document $document): Note
    {
        $note = new Note();
        $number = $document->document_number ?? 'FC01-1';

        if (strpos($number, '-') !== false) {
            $parts = explode('-', $number);
            $serie = $parts[0];
            $correlativo = $parts[1];
        } else {
            // Assume first 4 chars are series, rest is correlative
            $serie = substr($number, 0, 4);
            $correlativo = substr($number, 4);

            // Fallback if split fails or is empty
            if (empty($correlativo)) {
                $correlativo = '1';
            }
        }

        $note
            ->setUblVersion('2.1')
            ->setTipoDoc('07')
            ->setSerie($serie)
            ->setCorrelativo($correlativo)
            ->setFechaEmision(new \DateTime($document->issued_at))
            ->setTipDocAfectado(str_starts_with($document->invoice_number ?? '', 'F') ? '01' : '03')
            ->setNumDocfectado($document->invoice_number ?? '')
            ->setCodMotivo($document->credit_note_reason_code ?? '01')
            ->setDesMotivo(config('sunat.credit_note_reason_codes')[$document->credit_note_reason_code ?? '01'] ?? 'Anulación')
            ->setTipoMoneda($document->currency_code)
            ->setCompany($this->buildCompany($document))
            ->setClient($this->buildClient($document));

        $this->setTotals($note, $document);
        $note->setDetails($this->buildDetails($document));
        $note->setLegends($this->buildLegends($document));

        return $note;
    }

    protected function buildCompany(Document $document): Company
    {
        $company = $document->company;
        $address = (new Address())
            ->setUbigueo(setting('company.ubigeo', '150101'))
            ->setDepartamento('LIMA')
            ->setProvincia('LIMA')
            ->setDistrito('LIMA')
            ->setDireccion($company->address ?? setting('company.address', ''))
            ->setCodLocal('0000');

        return (new Company())
            ->setRuc($company->tax_number)
            ->setRazonSocial($company->name)
            ->setAddress($address);
    }

    protected function buildClient(Document $document): Client
    {
        $contact = $document->contact;
        $taxNumber = $contact->tax_number ?? '00000000';
        $tipoDoc = strlen($taxNumber) === 11 ? '6' : '1';

        return (new Client())
            ->setTipoDoc($tipoDoc)
            ->setNumDoc($taxNumber)
            ->setRznSocial($contact->name);
    }

    protected function setTotals(Note $note, Document $document): void
    {
        $gravado = $igv = 0;
        foreach ($document->items as $item) {
            $gravado += $item->total;
            $igv += $item->tax ?? 0;
        }

        $note
            ->setMtoOperGravadas($gravado)
            ->setMtoIGV($igv)
            ->setTotalImpuestos($igv)
            ->setMtoImpVenta($document->amount);
    }

    protected function buildDetails(Document $document): array
    {
        $details = [];
        foreach ($document->items as $item) {
            $details[] = (new SaleDetail())
                ->setCodProducto($item->item?->sku ?? 'PROD001')
                ->setUnidad('NIU')
                ->setCantidad($item->quantity)
                ->setMtoValorUnitario($item->price)
                ->setDescripcion($item->name)
                ->setMtoBaseIgv($item->total)
                ->setPorcentajeIgv(18.00)
                ->setIgv($item->tax ?? 0)
                ->setTipAfeIgv('10')
                ->setTotalImpuestos($item->tax ?? 0)
                ->setMtoValorVenta($item->total)
                ->setMtoPrecioUnitario($item->price * 1.18);
        }
        return $details;
    }

    protected function buildLegends(Document $document): array
    {
        $formatter = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
        $int = floor($document->amount);
        $dec = round(($document->amount - $int) * 100);
        return [(new Legend())->setCode('1000')->setValue("SON " . strtoupper($formatter->format($int)) . " CON {$dec}/100 SOLES")];
    }
}
