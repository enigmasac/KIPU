<?php

namespace Modules\Sunat\Services\Builders;

use App\Models\Document\Document;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;

class InvoiceBuilder
{
    public function build(Document $document): Invoice
    {
        $invoice = new Invoice();
        $number = $document->document_number ?? 'F001-1';

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

        // Calculate Detraction (SPOT) first to determine Operation Type
        $detractionAmount = 0;
        $detractionPercentage = 0;
        $detractionCode = '022'; // Default generic code
        $hasDetraction = false;

        foreach ($document->items as $item) {
            // Check if item has detraction enabled (from inventory/item module)
            $product = $item->item;
            if ($product && $product->is_detraction) {
                $hasDetraction = true;
                $percentage = $product->detraction_percentage;

                // Logic: prioritize highest percentage, update code accordingly
                if ($percentage > $detractionPercentage) {
                    $detractionPercentage = $percentage;
                    // Use code from this item if available, otherwise keep default or existing
                    if (!empty($product->detraction_code)) {
                        $detractionCode = $product->detraction_code;
                    }
                } elseif ($percentage == $detractionPercentage && !empty($product->detraction_code)) {
                    // if percentage is same, update code if current is default or empty
                    $detractionCode = $product->detraction_code;
                }
            }
        }

        $applyDetraction = $hasDetraction && $document->amount > 700;
        $tipoOperacion = $applyDetraction ? '1001' : '0101'; // 1001: Operación Sujeta a Detracción

        $invoice
            ->setUblVersion('2.1')
            ->setTipoOperacion($tipoOperacion)
            ->setTipoDoc(str_starts_with($number, 'F') ? '01' : '03')
            ->setSerie($serie)
            ->setCorrelativo($correlativo)
            ->setFechaEmision(new \DateTime($document->issued_at))
            ->setFormaPago($document->sale_type === 'credit'
                ? new FormaPagoCredito($document->amount)
                : new FormaPagoContado())
            ->setTipoMoneda($document->currency_code)
            ->setCompany($this->buildCompany($document))
            ->setClient($this->buildClient($document));

        $this->setTotals($invoice, $document);
        $invoice->setDetails($this->buildDetails($document));
        $invoice->setLegends($this->buildLegends($document));

        if ($document->sale_type === 'credit' && $document->installments->count() > 0) {
            $invoice->setCuotas($this->buildCuotas($document));
        }

        if ($applyDetraction) {
            $detractionAmount = round($document->amount * ($detractionPercentage / 100), 2);

            $invoice->setDetraccion(
                (new \Greenter\Model\Sale\Detraction())
                    ->setCodBienDetraccion($detractionCode)
                    ->setCodMedioPago('001') // Depósito en cuenta
                    ->setCtaBanco(setting('sunat.detraction_account') ?? '')
                    ->setPercent($detractionPercentage)
                    ->setMount($detractionAmount)
            );
        }

        return $invoice;
    }

    protected function buildCompany(Document $document): Company
    {
        $company = $document->company;
        $address = (new Address())
            ->setUbigueo(setting('company.ubigeo', '150101'))
            ->setDepartamento(setting('company.department', 'LIMA'))
            ->setProvincia(setting('company.province', 'LIMA'))
            ->setDistrito(setting('company.district', 'LIMA'))
            ->setDireccion($company->address ?? setting('company.address', 'Sin dirección'))
            ->setCodLocal('0000');

        return (new Company())
            ->setRuc($company->tax_number)
            ->setRazonSocial($company->name)
            ->setNombreComercial($company->name)
            ->setAddress($address);
    }

    protected function buildClient(Document $document): Client
    {
        $contact = $document->contact;
        $taxNumber = $contact->tax_number ?? '00000000';
        $tipoDoc = strlen($taxNumber) === 11 ? '6' : (strlen($taxNumber) === 8 ? '1' : '0');

        return (new Client())
            ->setTipoDoc($tipoDoc)
            ->setNumDoc($taxNumber)
            ->setRznSocial($contact->name);
    }

    protected function setTotals(Invoice $invoice, Document $document): void
    {
        $gravado = $exonerado = $inafecto = $igv = $isc = $baseIsc = 0;
        $valorVenta = 0;

        foreach ($document->items as $item) {
            // Fetch tax details
            $igvAmount = 0;
            $iscAmount = 0;
            $hasTax = false;
            $isGravado = true; // Assumption

            foreach ($item->taxes as $docTax) {
                // We access the related Tax model via the tax_id to get the sunat_code
                $sunatCode = \App\Models\Setting\Tax::where('id', $docTax->tax_id)->value('sunat_code');

                if ($sunatCode == '1000') {
                    $igvAmount += $docTax->amount;
                    $hasTax = true;
                } elseif ($sunatCode == '2000') {
                    $iscAmount += $docTax->amount;
                    $hasTax = true;
                } elseif (in_array($sunatCode, ['9997', '9998'])) {
                    $isGravado = false;
                    // Handle exonerated/inaffected logic if needed
                }
            }

            // Fallback for items with tax but no explicit code (assume IGV)
            if (!$hasTax && ($item->tax > 0)) {
                $igvAmount = $item->tax;
            }

            // Accumulate Valor Venta (Sum of Item Totals - Values)
            $valorVenta += $item->total;

            // Accumulate Bases
            // For IGV (Gravado), Base = Value + ISC
            if ($isGravado) {
                $gravado += ($item->total + $iscAmount);
            } else {
                // Logic for Exonerated/Inaffected would go here
                // For now, let's assume everything else falls into Exonerated/Inaffected based on flags
                // But simply, we just won't add to gravado if explicitly marked otherwise (not fully implemented yet)
            }

            // For ISC, Base = Value
            if ($iscAmount > 0) {
                $baseIsc += $item->total;
            }

            $igv += $igvAmount;
            $isc += $iscAmount;
        }

        $invoice
            ->setMtoOperGravadas($gravado)
            ->setMtoIGV($igv)
            ->setMtoISC($isc)
            ->setMtoBaseIsc($baseIsc)
            ->setTotalImpuestos($igv + $isc)
            ->setValorVenta($gravado)
            ->setSubTotal($document->amount)
            ->setMtoImpVenta($document->amount);
    }

    protected function buildDetails(Document $document): array
    {
        $details = [];
        foreach ($document->items as $item) {
            $igvAmount = 0;
            $iscAmount = 0;
            $igvRate = 18.00;
            $iscRate = 0;
            $hasTax = false;

            foreach ($item->taxes as $docTax) {
                $tax = \App\Models\Setting\Tax::find($docTax->tax_id);
                $sunatCode = $tax?->sunat_code;

                if ($sunatCode == '1000') {
                    $igvAmount += $docTax->amount;
                    $igvRate = $tax->rate;
                    $hasTax = true;
                } elseif ($sunatCode == '2000') {
                    $iscAmount += $docTax->amount;
                    $iscRate = $tax->rate;
                    $hasTax = true;
                }
            }

            if (!$hasTax && ($item->tax > 0)) {
                $igvAmount = $item->tax;
            }

            $detail = (new SaleDetail())
                ->setCodProducto($item->item?->sku ?? 'PROD001')
                ->setUnidad($item->item?->unit ?? 'NIU')
                ->setCantidad($item->quantity)
                ->setMtoValorUnitario($item->price)
                ->setDescripcion($item->name)
                ->setMtoBaseIgv($item->total + $iscAmount) // Base for IGV is Total + ISC
                ->setPorcentajeIgv($igvRate)
                ->setIgv($igvAmount)
                ->setTipAfeIgv('10') // Gravado - Operación Onerosa
                ->setTotalImpuestos($igvAmount + $iscAmount)
                ->setMtoValorVenta($item->total)
                ->setMtoPrecioUnitario($item->price + (($igvAmount + $iscAmount) / max($item->quantity, 1)));

            if ($iscAmount > 0) {
                $detail->setMtoBaseIsc($item->total);
                $detail->setPorcentajeIsc($iscRate);
                $detail->setIsc($iscAmount);
                $detail->setTipSisIsc('01'); // Sistema al Valor
            }

            $details[] = $detail;
        }
        return $details;
    }

    protected function buildLegends(Document $document): array
    {
        $formatter = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
        $int = floor($document->amount);
        $dec = round(($document->amount - $int) * 100);
        $currency = $document->currency_code === 'PEN' ? 'SOLES' : 'DOLARES';

        $legends = [(new Legend())->setCode('1000')->setValue("SON " . strtoupper($formatter->format($int)) . " CON {$dec}/100 {$currency}")];

        // Check for detraction to add specific legend
        foreach ($document->items as $item) {
            if ($item->item && $item->item->is_detraction && $document->amount > 700) {
                $legends[] = (new Legend())->setCode('2006')->setValue('Operación sujeta a detracción');
                break;
            }
        }

        return $legends;
    }

    protected function buildCuotas(Document $document): array
    {
        $cuotas = [];
        foreach ($document->installments as $inst) {
            $cuotas[] = (new Cuota())->setMonto($inst->amount)->setFechaPago(new \DateTime($inst->due_at));
        }
        return $cuotas;
    }
}
