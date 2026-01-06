<div class="print-template">
    <div class="row">
        <div class="col-100">
            <div class="text text-dark">
                @stack('title_input_start')
                <h3>
                    {{ $textDocumentTitle }}
                </h3>
                @stack('title_input_end')
            </div>
        </div>
    </div>

    {{-- SUNAT HEADER LAYOUT --}}
    <div class="row" style="margin-bottom: 20px;">
        {{-- COLUMNA IZQUIERDA: LOGO Y DATOS --}}
        <div class="col-60">
            {{-- LOGO --}}
            @if (!$hideCompanyLogo && !empty($logo))
                <div class="mb-3">
                    <img class="d-logo" src="{{ $logo }}" alt="{{ setting('company.name') }}" style="max-height: 80px;" />
                </div>
            @endif

            {{-- DATOS EMPRESA --}}
            <div class="text" style="color: #000 !important;">
                <strong style="font-size: 1.1em;">{{ setting('company.name') }}</strong>
                <br>
                {!! nl2br(setting('company.address')) !!}
                <br>
                @if (setting('company.phone'))
                    Teléfono: {{ setting('company.phone') }} <br>
                @endif
                @if (setting('company.email'))
                    Email: {{ setting('company.email') }}
                @endif
            </div>
        </div>

        {{-- COLUMNA DERECHA: CAJA RUC --}}
        <div class="col-40">
            <div class="sunat-box">
                <div class="sunat-text" style="font-size: 14px; font-weight: bold;">R.U.C. {{ setting('sunat.ruc') ?: setting('company.tax_number') }}</div>
                <div style="background-color: #f0f0f0; margin: 10px -15px; padding: 10px 0; border-top: 1px solid #000; border-bottom: 1px solid #000;">
                    <div class="sunat-text" style="font-size: 12px; font-weight: bold; text-transform: uppercase;">
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
                <div class="sunat-text" style="font-size: 14px; font-weight: bold; margin-top: 10px;">{{ $document->document_number }}</div>
            </div>
        </div>
    </div>

    {{-- CLIENTE INFO --}}
    <div class="row sunat-client-box">
        <div class="col-100">
            <table class="sunat-text" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <tr>
                    <td style="width: 15%; font-weight: bold; padding: 3px;">Cliente:</td>
                    <td class="sunat-text" style="width: 45%; padding: 3px;">{{ $document->contact_name }}</td>
                    <td style="width: 15%; font-weight: bold; padding: 3px;">Fecha Emisión:</td>
                    <td class="sunat-text" style="width: 25%; padding: 3px;">@date($document->issued_at)</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 3px;">RUC/DNI:</td>
                    <td class="sunat-text" style="padding: 3px;">{{ $document->contact_tax_number }}</td>
                    <td style="font-weight: bold; padding: 3px;">Moneda:</td>
                    <td class="sunat-text" style="padding: 3px;">{{ $document->currency_code }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 3px;">Dirección:</td>
                    <td class="sunat-text" style="padding: 3px;">{{ $document->contact_address }}</td>
                    <td style="font-weight: bold; padding: 3px;">Vencimiento:</td>
                    <td class="sunat-text" style="padding: 3px;">@date($document->due_at)</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; padding: 3px;">Condición:</td>
                    <td class="sunat-text" colspan="3" style="padding: 3px;">{{ $document->status == 'paid' ? 'CONTADO' : 'CRÉDITO' }}</td>
                </tr>
                @if($document->reference)
                    <tr>
                        <td style="font-weight: bold; padding: 3px;">O/C:</td>
                        <td class="sunat-text" colspan="3" style="padding: 3px;">{{ $document->reference }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    @if (!$hideItems)
        <div class="row">
            <div class="col-100">
                <div class="text extra-spacing">
                    <table class="lines lines-radius-border" style="width: 100%;">
                        <thead
                            style="background-color:{{ $backgroundColor }} !important; -webkit-print-color-adjust: exact;">
                            <tr>
                                <th class="text-white" style="width: 10%; text-align: center; padding: 5px;">CANT.</th>
                                <th class="text-white" style="width: 10%; text-align: center; padding: 5px;">UNIDAD</th>
                                <th class="text-white" style="width: 15%; text-align: center; padding: 5px;">CÓDIGO</th>
                                <th class="text-white" style="width: 30%; text-align: left; padding: 5px;">DESCRIPCIÓN</th>
                                <th class="text-white" style="width: 10%; text-align: right; padding: 5px;">V. UNIT</th>
                                <th class="text-white" style="width: 10%; text-align: right; padding: 5px;">P. UNIT</th>
                                <th class="text-white" style="width: 15%; text-align: right; padding: 5px;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody style="color: #000 !important;">
                            @if ($document->items->count())
                                @foreach($document->items as $item)
                                    @php
                                        // V. Unit: Base Imponible Unitante (Total sin IGV / Cantidad)
                                        $unit_value = $item->price; 
                                        
                                        // P. Unit: Precio con IGV Unitario (Total con IGV / Cantidad)
                                        // En Akaunting, item->total suele ser la base imponible de la linea.
                                        // Sumamos el tax de la linea para tener el total real con IGV.
                                        $line_total_with_tax = $item->total + $item->tax;
                                        $unit_price = $line_total_with_tax / max($item->quantity, 1);
                                    @endphp
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="text-align: center; padding: 5px;">{{ $item->quantity }}</td>
                                        <td style="text-align: center; padding: 5px;">{{ $item->sunat_unit_code ?? ($item->item?->sunat_unit_code ?? 'NIU') }}</td>
                                        <td style="text-align: center; padding: 5px;">{{ $item->sku }}</td>
                                        <td style="text-align: left; padding: 5px;">
                                            {{ $item->name }}
                                            @if($item->description)
                                                <br><small style="color: #666;">{{ $item->description }}</small>
                                            @endif
                                        </td>
                                        <td style="text-align: right; padding: 5px;">
                                            <x-money :amount="$unit_value" :currency="$document->currency_code" />
                                        </td>
                                        <td style="text-align: right; padding: 5px;">
                                            <x-money :amount="$unit_price" :currency="$document->currency_code" />
                                        </td>
                                        <td style="text-align: right; padding: 5px;">
                                            <x-money :amount="$line_total_with_tax" :currency="$document->currency_code" />
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">{{ trans('documents.empty_items') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="row mt-9 clearfix">
        <div class="col-60 float-left">
            <div class="text p-index-left break-words">
                @stack('notes_input_start')
                @if ($document->notes)
                    <p class="font-semibold">
                        {{ trans_choice('general.notes', 2) }}
                    </p>

                    {!! nl2br($document->notes) !!}
                @endif
                @stack('notes_input_end')

                {{-- SON: CANTIDAD EN LETRAS --}}
                <div style="margin-top: 20px; border: 1px solid #ccc; padding: 10px; border-radius: 5px; color: #000 !important;">
                    <strong style="color: #000;">SON:</strong> {{ $document->amount_in_words }}
                    @php
                        $has_detraction = false;
                        foreach ($document->items as $item) {
                            if ($item->item && $item->item->is_detraction && $document->amount > 700) {
                                $has_detraction = true;
                                break;
                            }
                        }
                    @endphp
                    @if($has_detraction)
                        <br><small style="color: #000;">OPERACIÓN SUJETA AL SISTEMA DE PAGO DE OBLIGACIONES TRIBUTARIAS</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-40 float-right text-right">
            {{-- TOTALES SUNAT --}}
            <div style="border: 1px solid #ccc; border-radius: 5px; overflow: hidden; color: #000 !important;">
                @foreach ($document->totals_sorted as $total)
                    @if ($total->code != 'total')
                        <div class="text border-bottom-1 py-1 px-3" style="color: #000 !important;">
                            <span class="float-left font-semibold" style="color: #000;">
                                {{ trans($total->title) }}:
                            </span>
                            <span style="color: #000;">
                                <x-money :amount="$total->amount" :currency="$document->currency_code" />
                            </span>
                        </div>
                    @else
                        <div class="text border-bottom-1 py-1 px-3" style="background-color: #f0f0f0; color: #000 !important;">
                            <span class="float-left font-bold" style="color: #000;">Importe Total:</span>
                            <span class="font-bold" style="color: #000;">
                                <x-money :amount="$total->amount" :currency="$document->currency_code" />
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- FOOTER FINAL: QR Y BANCOS --}}
    <div class="row mt-9" style="border-top: 1px solid #eee; padding-top: 10px;">
        <div class="col-25">
            {{-- QR CODE --}}
            <div style="text-align: center;">
                @if($document->sunat_qr_image)
                    <img src="{{ $document->sunat_qr_image }}" style="width: 120px; height: 120px;" alt="QR Code" />
                @else
                    <div class="sunat-text" style="font-size: 10px; padding: 10px; border: 1px dashed #ccc;">QR Code</div>
                @endif
            </div>
        </div>
        <div class="col-35">
            {{-- HASH Y OTROS --}}
            <div style="font-size: 0.8em; margin-top: 20px; color: #000 !important;">
                @if($document->latest_sunat_emission)
                    <strong style="color: #000;">Digest Value:</strong><br>
                    <span style="word-break: break-all; color: #000;">{{ $document->latest_sunat_emission->hash }}</span>
                @endif
                <br><br>
                <span style="color: #000;">Representación impresa de la {{ $document->type == 'invoice' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA' }}, consulte en www.sunat.gob.pe</span>
            </div>
        </div>
        <div class="col-40">
            {{-- BANCOS --}}
            <div style="border: 1px solid #eee; padding: 10px; border-radius: 5px; background-color: #fafafa; color: #000 !important;">
                <h5 style="margin: 0 0 5px 0; border-bottom: 1px solid #ddd; padding-bottom: 3px; color: #000;">Cuentas Bancarias</h5>
                @php
                    $accounts = \App\Models\Banking\Account::where('enabled', 1)->get();
                @endphp
                @foreach($accounts as $acc)
                    <div style="font-size: 0.85em; margin-bottom: 3px; color: #000;">
                        <strong style="color: #000;">{{ $acc->bank_name ?: $acc->name }}:</strong><br>
                        {{ $acc->currency_code }}: {{ $acc->number }}
                    </div>
                @endforeach
                @if(setting('company.sunat_bn_account'))
                    <div style="font-size: 0.85em; margin-top: 5px; border-top: 1px solid #eee; padding-top: 3px; color: #000;">
                        <strong style="color: #000;">Banco de la Nación (Detracciones):</strong><br>
                        {{ setting('company.sunat_bn_account') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if (!$hideFooter)
        @if ($document->footer)
            <div class="row mt-4">
                <div class="col-100 text-center" style="font-size: 0.8em; color: #333;">
                    {!! nl2br($document->footer) !!}
                </div>
            </div>
        @endif
    @endif
</div>