<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('general.debit_notes', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-documents.form.content
            type="debit-note"
            :document="$debit_note"
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
        :items="$debit_note->items"
        :document="$debit_note"
    />
</x-layouts.admin>
