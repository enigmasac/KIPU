<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.debit_notes', 2) }}
    </x-slot>

    <x-slot name="favorite"
            :title="trans_choice('general.debit_notes', 2)"
            icon="description"
            route="sales.debit-notes.index"
    ></x-slot>

    <x-slot name="moreButtons">
        <x-documents.index.more-buttons type="debit-note" />
    </x-slot>

    <x-slot name="content">
        <x-documents.index.content
            type="debit-note"
            page="debit_notes"
            :documents="$debit_notes"
            active-tab="debit-note"
            hide-due-at
            hide-import
            hide-recurring-templates
            :hide-create="true"
            :description="trans('debit-notes.description')"
        />
    </x-slot>

    <x-documents.script type="debit-note" />
</x-layouts.admin>
