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
            <div class="text">
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
            <div style="border: 2px solid #000; padding: 15px; text-align: center; border-radius: 8px;">
                <h4 style="margin: 0; font-weight: bold;">R.U.C. {{ setting('company.tax_number') }}</h4>
                <div style="background-color: #f0f0f0; margin: 10px -15px; padding: 5px 0;">
                    <h4 style="margin: 0; font-weight: bold; text-transform: uppercase;">
                        {{ $document->type == 'invoice' ? 'FACTURA ELECTRÓNICA' : ($document->type == 'credit-note' ? 'NOTA DE CRÉDITO' : 'BOLETA DE VENTA') }}
                    </h4>
                </div>
                <h4 style="margin: 0; font-weight: bold;">{{ $document->document_number }}</h4>
            </div>
        </div>
    </div>

    {{-- CLIENTE INFO --}}
    <div class="row" style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-bottom: 20px;">
        <div class="col-100">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 15%; font-weight: bold;">Cliente:</td>
                    <td style="width: 50%;">{{ $document->contact_name }}</td>
                    <td style="width: 15%; font-weight: bold;">Fecha Emisión:</td>
                    <td style="width: 20%;">@date($document->issued_at)</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">RUC/DNI:</td>
                    <td>{{ $document->contact_tax_number }}</td>
                    <td style="font-weight: bold;">Moneda:</td>
                    <td>{{ $document->currency_code }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Dirección:</td>
                    <td colspan="3">{{ $document->contact_address }}</td>
                </tr>
                @if($document->due_at)
                    <tr>
                        <td style="font-weight: bold;">Vencimiento:</td>
                        <td>@date($document->due_at)</td>
                        <td style="font-weight: bold;">Condición:</td>
                        <td>{{ $document->status == 'paid' ? 'Contado' : 'Crédito' }}</td>
                    </tr>
                @endif
                @if($document->reference)
                    <tr>
                        <td style="font-weight: bold;">O/C:</td>
                        <td colspan="3">{{ $document->reference }}</td>
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
                                <th class="text-white" style="width: 10%; text-align: center;">CANT.</th>
                                <th class="text-white" style="width: 10%; text-align: center;">UNIDAD</th>
                                <th class="text-white" style="width: 15%; text-align: center;">CÓDIGO</th>
                                <th class="text-white" style="width: 30%; text-align: left;">DESCRIPCIÓN</th>
                                <th class="text-white" style="width: 10%; text-align: right;">V. UNIT</th>
                                <th class="text-white" style="width: 10%; text-align: right;">P. UNIT</th>
                                <th class="text-white" style="width: 15%; text-align: right;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($document->items->count())
                                @foreach($document->items as $item)
                                    @php
                                        // Calculate Unit Value (Valor Unitario)
                                        // V. Unit = Total (without tax) / Quantity
                                        // We assume item->price includes tax depending on setting, but standard SUNAT invoice shows Base Imponible

                                        $unit_value = $item->price; 
                                    @endphp
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="text-align: center;">{{ $item->quantity }}</td>
                                        <td style="text-align: center;">{{ $item->unit ?? 'NIU' }}</td>
                                        <td style="text-align: center;">{{ $item->sku }}</td>
                                        <td style="text-align: left;">
                                            {{ $item->name }}
                                            @if($item->description)
                                                <br><small>{{ $item->description }}</small>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <x-money :amount="$item->price" :currency="$document->currency_code" />
                                        </td>
                                        <td style="text-align: right;">
                                            @php
                                                $unit_price = $item->total / max($item->quantity, 1);
                                            @endphp
                                            <x-money :amount="$unit_price" :currency="$document->currency_code" />
                                        </td>
                                        <td style="text-align: right;">
                                            <x-money :amount="$item->total" :currency="$document->currency_code" />
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
                <div style="margin-top: 20px; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                    <strong>SON:</strong> {{ $document->amount_in_words }}
                </div>
            </div>
        </div>

        <div class="col-40 float-right text-right">
            {{-- TOTALES SUNAT --}}
            <div style="border: 1px solid #ccc; border-radius: 5px; overflow: hidden;">
                @foreach ($document->totals_sorted as $total)
                    @if ($total->code != 'total')
                        <div class="text border-bottom-1 py-1 px-3">
                            <span class="float-left font-semibold">
                                {{ trans($total->title) }}:
                            </span>
                            <span>
                                <x-money :amount="$total->amount" :currency="$document->currency_code" />
                            </span>
                        </div>
                    @else
                        <div class="text border-bottom-1 py-1 px-3" style="background-color: #f0f0f0;">
                            <span class="float-left font-bold">Importe Total:</span>
                            <span class="font-bold">
                                <x-money :amount="$total->amount" :currency="$document->currency_code" />
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- FOOTER FINAL: QR Y BANCOS --}}
    <div class="row mt-9" style="border-top: 1px solid #eee; pt-4">
        <div class="col-25">
            {{-- QR CODE --}}
            @php
                $qrUrl = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=" . urlencode($document->sunat_qr_content);
            @endphp
            <div style="text-align: center;">
                <img src="{{ $qrUrl }}" style="width: 120px; height: 120px;" alt="QR Code" />
            </div>
        </div>
        <div class="col-35">
            {{-- HASH Y OTROS --}}
            <div style="font-size: 0.8em; margin-top: 20px;">
                @if($document->latest_sunat_emission)
                    <strong>Digest Value:</strong><br>
                    <span style="word-break: break-all;">{{ $document->latest_sunat_emission->hash }}</span>
                @endif
                <br><br>
                Representación impresa de la
                {{ $document->type == 'invoice' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA' }}, consulte en
                www.sunat.gob.pe
            </div>
        </div>
        <div class="col-40">
            {{-- BANCOS --}}
            <div style="border: 1px solid #eee; padding: 10px; border-radius: 5px; background-color: #fafafa;">
                <h5 style="margin: 0 0 5px 0; border-bottom: 1px solid #ddd; padding-bottom: 3px;">Cuentas Bancarias
                </h5>
                @php
                    $accounts = \App\Models\Banking\Account::where('enabled', 1)->get();
                @endphp
                @foreach($accounts as $acc)
                    <div style="font-size: 0.85em; margin-bottom: 3px;">
                        <strong>{{ $acc->bank_name ?: $acc->name }}:</strong><br>
                        {{ $acc->currency_code }}: {{ $acc->number }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if (!$hideFooter)
        @if ($document->footer)
            <div class="row mt-4">
                <div class="col-100 text-center" style="font-size: 0.8em; color: #666;">
                    {!! nl2br($document->footer) !!}
                </div>
            </div>
        @endif
    @endif
</div>