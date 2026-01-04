<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.new', ['type' => trans_choice('general.debit_notes', 1)]) }}
    </x-slot>

    <x-slot name="favorite"
            title="{{ trans('general.title.new', ['type' => trans_choice('general.debit_notes', 1)]) }}"
            icon="description"
            route="sales.debit-notes.create"
    ></x-slot>

    <x-slot name="content">
        <x-documents.form.content
            type="debit-note"
            hide-company
            hide-footer
            hide-edit-item-columns
            hide-due-at
            hide-order-number
            hide-recurring
            hide-attachment
            text-item-description="general.description"
            is-purchase-price
        />
    </x-slot>

    <x-documents.script
        type="debit-note"
        :items="$invoice_items ?? []"
    />
</x-layouts.admin>
