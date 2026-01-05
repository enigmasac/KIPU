<x-form.section>
    <x-slot name="body">
        <x-documents.form.metadata type="{{ $type }}" />

        {{-- SUNAT PATCH: Forzar campos en Recurrentes si Metadata falla --}}
        @if ($type == 'invoice-recurring')
            <div class="grid sm:grid-cols-7 sm:col-span-6 gap-x-8 gap-y-6 my-3.5">
                <div class="sm:col-span-3"></div> <!-- Espaciador para alinear a la derecha -->
                <div class="sm:col-span-4 grid sm:grid-cols-4 gap-x-8 gap-y-6">
                    <x-form.group.select 
                        name="sunat_document_type" 
                        label="Tipo de Comprobante" 
                        :options="['01' => 'Factura', '03' => 'Boleta']" 
                        :selected="data_get($document, 'sunat_document_type', '01')" 
                        v-model="form.sunat_document_type" 
                        form-group-class="sm:col-span-2" 
                    />

                    <x-form.group.select 
                        name="sunat_operation_type" 
                        label="Tipo de Operación" 
                        :options="['01' => 'Venta Normal', '02' => 'Venta Gratuita']" 
                        :selected="data_get($document, 'sunat_operation_type', '01')" 
                        form-group-class="sm:col-span-2" 
                    />

                    <x-form.group.select 
                        name="sale_type" 
                        label="Tipo de Venta" 
                        :options="['cash' => 'Venta al Contado', 'credit' => 'Venta al Crédito']" 
                        v-model="form.sale_type" 
                        :selected="data_get($document, 'sale_type', 'cash')" 
                        form-group-class="sm:col-span-2" 
                    />
                </div>
            </div>
        @endif

        @if ($type == 'invoice')
            <x-documents.form.installments />
        @endif

        <x-documents.form.items type="{{ $type }}" />

        <x-documents.form.totals type="{{ $type }}" />

        <x-documents.form.note type="{{ $type }}" />
    </x-slot>
</x-form.section>
