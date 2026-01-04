<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.new', ['type' => trans_choice('general.credit_notes', 1)]) }}
    </x-slot>

    <x-slot name="favorite"
            title="{{ trans('general.title.new', ['type' => trans_choice('general.credit_notes', 1)]) }}"
            icon="description"
            route="sales.credit-notes.create"
    ></x-slot>

    <x-slot name="content">
        <x-documents.form.content
            type="credit-note"
            :contact="$contact"
            hide-company
            hide-footer
            hide-edit-item-columns
            hide-due-at
            hide-order-number
            hide-recurring
            hide-attachment
            text-item-description="general.description"
        />
    </x-slot>

    <x-documents.script
        type="credit-note"
        :items="$invoice_items ?? []"
        :document="$invoice"
    />
</x-layouts.admin>

{{-- <script src="{{ 'public/js/credit_notes/create.js'}}"></script>  --}}
