<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => $recurring_invoice->document_number]) }}
    </x-slot>

    <x-slot name="content">
        <x-documents.form.content type="invoice-recurring" :document="$recurring_invoice" show-recurring hide-send-to />
    </x-slot>

    <x-documents.script type="invoice-recurring" :document="$recurring_invoice" :items="$recurring_invoice->items()->get()" />
</x-layouts.admin>
