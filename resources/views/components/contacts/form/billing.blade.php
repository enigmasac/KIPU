<x-form.section>
    <x-slot name="head">
        <x-form.section.head
            title="{{ trans($textSectionBillingTitle) }}"
            description="{{ trans($textSectionBillingDescription) }}"
        />
    </x-slot>

    <x-slot name="body">
        <x-form.group.select name="document_type" label="Tipo de Documento" :options="['RUC' => 'RUC', 'DNI' => 'DNI', 'CE' => 'CARNET DE EXTRANJERIA', 'PAS' => 'PASAPORTE']" :selected="! empty($contact) ? $contact->document_type : 'RUC'" />

        @if (! $hideTaxNumber)
            <x-form.group.text name="tax_number" label="RUC / DNI / Otros" not-required />
        @endif

        <x-form.group.text name="name" label="{{ trans('general.name') }}" />

        <x-form.group.select name="default_sunat_document_type" label="Comprobante Predeterminado" :options="['01' => 'Factura', '03' => 'Boleta']" :selected="! empty($contact) ? $contact->default_sunat_document_type : '01'" />

        <x-form.group.select name="default_sunat_operation_type" label="Tipo de Operación Predeterminado" :options="['01' => 'Venta Normal', '02' => 'Venta Gratuita']" :selected="! empty($contact) ? $contact->default_sunat_operation_type : '01'" />

        @if (! $hideCurrency)
            <x-form.group.currency />
        @endif
    </x-slot>
</x-form.section>
