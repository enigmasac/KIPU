<div class="sm:col-span-2">
    <akaunting-select
        class="relative"
        id="invoice_id"
        title="Factura de Referencia (Obligatorio)"
        placeholder="- Seleccione Factura -"
        name="invoice_id"
        :form-classes="[{'has-error': form.errors.get('invoice_id') }]"
        :options="{{ json_encode(collect($reference_invoices)->map(function($name, $id) { return ['id' => $id, 'name' => $name]; })->values()) }}"
        :dynamic-options="form.invoices"
        :value="{{ $pre_selected_invoice_id ?? 'form.invoice_id' }}"
        @interface="form.errors.clear('invoice_id'); form.invoice_id = $event; if (window.onSelectReferenceInvoice) { window.onSelectReferenceInvoice($event); }"
        :disabled="form.invoice_id"
        :readonly="form.invoice_id"
        clearable
        :not-required="false"
        :form-error="form.errors.get('invoice_id')"
        loading-text="Cargando..."
        no-data-text="No hay facturas disponibles"
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
        id="credit_note_reason_code"
        title="Motivo SUNAT (Catálogo 09)"
        placeholder="- Seleccione Motivo -"
        name="credit_note_reason_code"
        :form-classes="[{'has-error': form.errors.get('credit_note_reason_code') }]"
        :options="{{ json_encode(collect($sunat_reasons)->map(function($name, $id) { return ['id' => $id, 'name' => $name]; })->values()) }}"
        :value="form.credit_note_reason_code"
        @interface="form.errors.clear('credit_note_reason_code'); form.credit_note_reason_code = $event"
        clearable
        :not-required="false"
        :form-error="form.errors.get('credit_note_reason_code')"
    >
        <div class="input-group-prepend absolute right-2 bottom-3 text-light-gray">
            <span class="input-group-text">
                <span class="material-icons w-4 h-5 text-sm">info</span>
            </span>
        </div>
    </akaunting-select>
</div>

<div
    v-if="form && form.errors && (form.errors.get('invoice_id') || form.errors.get('credit_note_reason_code'))"
    class="fixed bottom-6 left-6 z-50 max-w-md"
>
    <div class="flex items-start space-x-2 bg-red-100 text-red-700 border border-red-300 rounded-lg px-4 py-3 shadow">
        <span class="material-icons-outlined text-red-700">error_outline</span>
        <div class="text-sm">
            <div class="font-semibold">Faltan datos obligatorios</div>
            <div v-if="form.errors.get('invoice_id')">La factura de referencia es obligatoria.</div>
            <div v-if="form.errors.get('credit_note_reason_code')">El motivo SUNAT es obligatorio.</div>
        </div>
    </div>
</div>
