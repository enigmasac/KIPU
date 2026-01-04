<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.debit_notes', 2) }}
    </x-slot>

    <x-slot name="favorite"
            :title="trans_choice('general.debit_notes', 2)"
            icon="description"
            route="purchases.debit-notes.index"
    ></x-slot>

    {{--TODO: fix getting "permission-create"--}}
    <x-slot name="buttons">
        <x-documents.index.buttons
            type="debit-note"
            check-create-permission
            permission-create="create-purchases-debit-notes"
        />
    </x-slot>

    <x-slot name="moreButtons">
        <x-documents.index.more-buttons type="debit-note" />
    </x-slot>

    {{--TODO: fix getting page in the App\Traits\ViewComponents
    replace dashes with undescore
    --}}
    <x-slot name="content">
        <x-documents.index.content
            type="debit-note"
            page="debit_notes"
            :documents="$debit_notes"
            active-tab="debit-note"
            hide-due-at
            hide-import
            hide-recurring-templates
        />
    </x-slot>

    <x-documents.script type="debit-note" />
</x-layouts.admin>
