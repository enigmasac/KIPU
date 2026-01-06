<div class="print-template">
    {{-- SUNAT HEADER LAYOUT - RESTRUCTURED (Logo+Data Left | RUC Right) --}}
    {{-- SUNAT HEADER LAYOUT - RESTRUCTURED (Logo+Data Left | RUC Right) --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            {{-- COLUMNA IZQUIERDA: LOGO (ROJO) Y DATOS EMPRESA (AMARILLO) --}}
            <td style="width: 55%; vertical-align: top; padding: 0 15px 0 0;">
                {{-- Bloque Rojo: Logo --}}
                @if (!$hideCompanyLogo && !empty($logo))
                    <div style="margin-bottom: 5px; text-align: left;">
                        <img src="{{ $logo }}" alt="{{ setting('company.name') }}" style="max-height: 110px; width: auto; max-width: 100%;" />
                    </div>
                @endif

                {{-- Bloque Amarillo: Datos Empresa --}}
                <div class="sunat-text" style="font-size: 10px; line-height: 1.3;">
                    <strong style="font-size: 11px;">{{ setting('company.name') }}</strong><br>
                    {!! nl2br(setting('company.address')) !!}
                    @if(setting('company.city'))
                         , {{ setting('company.city') }}
                    @endif
                    @if(setting('company.zip_code'))
                         - {{ setting('company.zip_code') }}
                    @endif
                    <br>
                    @if (setting('company.phone'))
                        Tel: {{ setting('company.phone') }}
                    @endif
                    @if (setting('company.email'))
                         | {{ setting('company.email') }}
                    @endif
                </div>
            </td>

            {{-- COLUMNA DERECHA: CAJA RUC --}}
            <td style="width: 45%; vertical-align: top; padding: 0; text-align: right;">
                <div class="sunat-box" style="border: 2px solid #000; display: inline-block; width: 100%; box-sizing: border-box; border-radius: 8px; overflow: hidden; padding: 0;">
                    <div class="sunat-text" style="padding: 6px 0; font-size: 14px; font-weight: bold;">R.U.C. {{ setting('sunat.ruc') ?: setting('company.tax_number') }}</div>
                    <div style="background-color: #f0f0f0; padding: 5px 0; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                        <div class="sunat-text" style="font-size: 13px; font-weight: bold; text-transform: uppercase;">
                            @php
                                $doc_type_label = match($document->sunat_document_type) {
                                    '01' => 'FACTURA ELECTRÓNICA',
                                    '03' => 'BOLETA DE VENTA',
                                    '07' => 'NOTA DE CRÉDITO',
                                    '08' => 'NOTA DE DÉBITO',
                                    default => ($document->type == 'invoice' ? 'FACTURA ELECTRÓNICA' : ($document->type == 'credit-note' ? 'NOTA DE CRÉDITO' : 'BOLETA DE VENTA'))
                                };
                            @endphp
                            {{ $doc_type_label }}
                        </div>
                    </div>
                    <div class="sunat-text" style="padding: 6px 0; font-size: 14px; font-weight: bold;">{{ $document->document_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- CLIENTE INFO - COMPACTO 2 FILAS --}}
    <div class="row sunat-client-box" style="padding: 4px 6px; margin-bottom: 6px;">
        <div class="col-100">
            <table class="sunat-text" style="width: 100%; border-collapse: collapse; font-size: 9px; line-height: 1.3;">
                <tr>
                    <td style="width: 50px; font-weight: bold; padding: 1px 2px;">Cliente:</td>
                    <td class="sunat-text" style="padding: 1px 2px;">{{ $document->contact_name }}</td>
                    <td style="width: 55px; font-weight: bold; padding: 1px 2px;">RUC/DNI:</td>
                    <td class="sunat-text" style="width: 90px; padding: 1px 2px;">{{ $document->contact_tax_number }}</td>
                    <td style="width: 45px; font-weight: bold; padding: 1px 2px;">Emisión:</td>
                    <td class="sunat-text" style="width: 70px; padding: 1px 2px; white-space: nowrap;">{{ $document->issued_at ? $document->issued_at->format('d/m/Y') : '' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px 2px;">Dirección:</td>
                    <td class="sunat-text" colspan="3" style="padding: 1px 2px;">{{ $document->contact_address }}@if($document->contact_city), {{ $document->contact_city }}@endif @if($document->contact_zip_code) - {{ $document->contact_zip_code }}@endif</td>
                    <td style="font-weight: bold; padding: 1px 2px;">Venc.:</td>
                    <td class="sunat-text" style="padding: 1px 2px; white-space: nowrap;">{{ $document->due_at ? $document->due_at->format('d/m/Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 1px 2px;">Condición:</td>
                    <td class="sunat-text" style="padding: 1px 2px;">{{ $document->status == 'paid' ? 'CONTADO' : 'CRÉDITO' }}</td>
                    <td style="font-weight: bold; padding: 1px 2px;">Moneda:</td>
                    <td class="sunat-text" style="padding: 1px 2px;">{{ $document->currency_code == 'PEN' ? 'SOLES' : $document->currency_name }}</td>
                    @if($document->reference)
                        <td style="font-weight: bold; padding: 1px 2px;">O/C:</td>
                        <td class="sunat-text" style="padding: 1px 2px;">{{ $document->reference }}</td>
                    @else
                        <td colspan="2"></td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    {{-- ITEMS TABLE - COMPACTO --}}
    @if (!$hideItems)
        <div class="row">
            <div class="col-100">
                <table class="lines lines-radius-border" style="width: 100%; font-size: 9px;">
                    <thead style="background-color:{{ $backgroundColor }} !important; -webkit-print-color-adjust: exact;">
                        <tr>
                            <th class="text-white" style="width: 8%; text-align: center; padding: 3px;">CANT.</th>
                            <th class="text-white" style="width: 8%; text-align: center; padding: 3px;">UM</th>
                            <th class="text-white" style="width: 12%; text-align: center; padding: 3px;">CÓDIGO</th>
                            <th class="text-white" style="width: 37%; text-align: left; padding: 3px;">DESCRIPCIÓN</th>
                            <th class="text-white" style="width: 11%; text-align: right; padding: 3px;">V.UNIT</th>
                            <th class="text-white" style="width: 11%; text-align: right; padding: 3px;">P.UNIT</th>
                            <th class="text-white" style="width: 13%; text-align: right; padding: 3px;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="sunat-text">
                        @if ($document->items->count())
                            @foreach($document->items as $item)
                                @php
                                    $unit_value = $item->price; 
                                    $line_total_with_tax = $item->total + $item->tax;
                                    $unit_price = $line_total_with_tax / max($item->quantity, 1);
                                @endphp
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="text-align: center; padding: 3px;">{{ $item->quantity }}</td>
                                    <td style="text-align: center; padding: 3px;">{{ $item->sunat_unit_code ?? ($item->item?->sunat_unit_code ?? 'NIU') }}</td>
                                    <td style="text-align: center; padding: 3px;">{{ $item->sku }}</td>
                                    <td style="text-align: left; padding: 3px;">{{ $item->name }}</td>
                                    <td style="text-align: right; padding: 3px;"><x-money :amount="$unit_value" :currency="$document->currency_code" /></td>
                                    <td style="text-align: right; padding: 3px;"><x-money :amount="$unit_price" :currency="$document->currency_code" /></td>
                                    <td style="text-align: right; padding: 3px;"><x-money :amount="$line_total_with_tax" :currency="$document->currency_code" /></td>
                                </tr>
                            @endforeach
                        @else
                            <tr><td colspan="7" class="text-center">{{ trans('documents.empty_items') }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TOTALES Y SON - COMPACTO --}}
    <div class="row clearfix" style="margin-top: 8px;">
        <div class="col-60 float-left">
            @if ($document->notes)
                <div class="sunat-text" style="font-size: 8px; margin-bottom: 5px;">
                    <strong>Notas:</strong> {!! nl2br($document->notes) !!}
                </div>
            @endif
            <div class="sunat-text" style="border: 1px solid #ccc; padding: 5px; border-radius: 3px; font-size: 9px;">
                <strong>SON:</strong> {{ $document->amount_in_words }}
                <br>
                <div style="margin-top: 3px;">
                    <strong>ESTADO:</strong> {{ $document->status == 'paid' ? 'PAGADO' : 'PENDIENTE DE PAGO' }}
                </div>
            </div>
        </div>

        <div class="col-40 float-right text-right">
            <div style="border: 1px solid #ccc; border-radius: 3px; overflow: hidden; font-size: 9px;">
                @foreach ($document->totals_sorted as $total)
                    @if ($total->code != 'total')
                        <div class="sunat-text" style="padding: 2px 5px; border-bottom: 1px solid #eee;">
                            <span class="float-left">{{ trans($total->title) }}:</span>
                            <span><x-money :amount="$total->amount" :currency="$document->currency_code" /></span>
                        </div>
                    @else
                        <div class="sunat-text" style="padding: 3px 5px; background-color: #f0f0f0; font-weight: bold;">
                            <span class="float-left">TOTAL:</span>
                            <span><x-money :amount="$total->amount" :currency="$document->currency_code" /></span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- FOOTER: QR Y CUENTAS (Lado a Lado) --}}
    <div class="row" style="margin-top: 10px; border-top: 1px solid #ddd; padding-top: 8px;">
        {{-- QR CODE --}}
        <div style="display: inline-block; width: 14%; vertical-align: top; text-align: center;">
            @if($document->sunat_qr_image)
                <img src="{{ $document->sunat_qr_image }}" style="width: 80px; height: 80px;" alt="QR" />
            @endif
        </div>

        {{-- CUENTAS BANCARIAS --}}
        <div style="display: inline-block; width: 85%; vertical-align: top; padding-left: 10px;">
            <strong style="font-size: 8px; border-bottom: 1px solid #ddd; display: block; padding-bottom: 2px; margin-bottom: 3px; color: #000;">Cuentas Bancarias</strong>
            <div class="sunat-text" style="font-size: 8px;">
                @php
                    $accounts = \App\Models\Banking\Account::where('enabled', 1)->get();
                @endphp
                
                {{-- Grid de Cuentas --}}
                <div style="width: 100%;">
                    @forelse($accounts as $acc)
                        <div style="display: inline-block; width: 48%; margin-bottom: 2px;">
                            <strong>{{ $acc->bank_name ?: $acc->name }}:</strong> {{ $acc->currency_code }} {{ $acc->number }}
                        </div>
                    @empty
                        @if(!setting('company.sunat_bn_account'))
                            <div style="color: #999;">Sin cuentas configuradas</div>
                        @endif
                    @endforelse
                    
                    @if(setting('company.sunat_bn_account'))
                        <div style="display: inline-block; width: 100%; margin-top: 2px; padding-top: 2px; border-top: 1px dotted #ccc;">
                            <strong>Banco de la Nación (Detracciones):</strong> {{ setting('company.sunat_bn_account') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER FINAL: HASH, LINK, LEGAL, KIPU --}}
    <div class="row" style="margin-top: 5px;">
        <div class="col-100 text-center sunat-text" style="font-size: 7px; line-height: 1.3;">
            {{-- Hash y Link Online --}}
            <div style="margin-bottom: 3px;">
                @if($document->latest_sunat_emission && $document->latest_sunat_emission->hash)
                    <strong>Hash:</strong> {{ $document->latest_sunat_emission->hash }} &nbsp;|&nbsp; 
                @endif
                @php
                    $invoiceUrl = \Illuminate\Support\Facades\URL::signedRoute('signed.invoices.show', [$document->id]);
                @endphp
                <strong>Ver documento online:</strong> <span style="word-break: break-all; color: blue;">{{ $invoiceUrl }}</span>
            </div>

            {{-- Textos Legales --}}
            Representación impresa de {{ $doc_type_label }}, consulte en www.sunat.gob.pe<br>
            @if ($document->footer)
                {!! nl2br($document->footer) !!}<br>
            @endif
            
            {{-- Branding --}}
            <div style="margin-top: 2px; font-weight: bold; color: #555;">
                Emitido con KIPU ERP | www.kipuerp.com
            </div>
        </div>
    </div>


</div>