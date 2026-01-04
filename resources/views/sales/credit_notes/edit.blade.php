<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('general.credit_notes', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-documents.form.content
            type="credit-note"
            :document="$credit_note"
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
        :items="$credit_note->items"
        :document="$credit_note"
    />
</x-layouts.admin>
