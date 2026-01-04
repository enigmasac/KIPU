@php
    $selected_invoice_id = $pre_selected_invoice_id ?? null;
    $is_locked = !empty($selected_invoice_id);
    $selected_invoice = $selected_invoice ?? null;

    $option_list = $is_locked
        ? [
            [
                'id' => $selected_invoice_id,
                'name' => optional($selected_invoice)->document_number ?? trans('general.na'),
            ],
        ]
        : collect($reference_invoices)->map(function ($name, $id) {
            return ['id' => $id, 'name' => $name];
        })->values()->toArray();
@endphp

<div class="sm:col-span-2">
    <akaunting-select
        class="relative"
        id="invoice_id"
        title="Comprobante (Obligatorio)"
        placeholder="- Seleccione Comprobante -"
        name="invoice_id"
        :form-classes="[{'has-error': form.errors.get('invoice_id') }]"
        :options="{{ json_encode($option_list) }}"
        :dynamic-options="form.invoices"
        :value="{{ $selected_invoice_id ?? 'form.invoice_id' }}"
        @interface="form.errors.clear('invoice_id'); form.invoice_id = $event; window.onSelectReferenceInvoice($event)"
        :disabled="{{ $is_locked ? 'true' : 'false' }}"
        :readonly="{{ $is_locked ? 'true' : 'false' }}"
        clearable
        :not-required="false"
        :form-error="form.errors.get('invoice_id')"
        loading-text="Cargando..."
        no-data-text="Seleccione un cliente primero"
    >
        <div class="input-group-prepend absolute right-2 bottom-3 text-light-gray">
            <span class="input-group-text">
                <span class="material-icons w-4 h-5 text-sm">file_present</span>
            </span>
        </div>
    </akaunting-select>

</div>

<div class="sm:col-span-2">
    <akaunting-select
        class="relative"
        id="debit_note_reason_code"
        title="Motivo SUNAT (Catálogo 08)"
        placeholder="- Seleccione Motivo -"
        name="debit_note_reason_code"
        :options="{{ json_encode(collect($sunat_reasons)->map(function($name, $id) { return ['id' => $id, 'name' => $name]; })->values()) }}"
        v-model="form.debit_note_reason_code"
        @interface="form.debit_note_reason_code = $event"
        clearable
        :not-required="false"
    >
        <div class="input-group-prepend absolute right-2 bottom-3 text-light-gray">
            <span class="input-group-text">
                <span class="material-icons w-4 h-5 text-sm">info</span>
            </span>
        </div>
    </akaunting-select>
</div>
