<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.credit_notes', 2) }}
    </x-slot>

    <x-slot name="favorite"
            :title="trans_choice('general.credit_notes', 2)"
            icon="description"
            route="sales.credit-notes.index"
    ></x-slot>

{{--TODO: fix getting "permission-create"--}}
    <x-slot name="buttons">
        <x-documents.index.buttons
            type="credit-note"
            check-create-permission
            permission-create="create-sales-credit-notes"
            hide-create
        />
    </x-slot>

    <x-slot name="moreButtons">
        <x-documents.index.more-buttons type="credit-note" />
    </x-slot>

{{--TODO: fix getting page in the App\Traits\ViewComponents
replace dashes with undescore
--}}
    <x-slot name="content">
        <x-documents.index.content
            type="credit-note"
            page="credit_notes"
            :documents="$credit_notes"
            active-tab="credit-note"
            hide-create
            hide-due-at
            hide-import
            hide-recurring-templates
            url-docs-path="https://akaunting.com/docs/app-manual/accounting/credit-debit-notes"
        />
    </x-slot>

    <x-documents.script type="credit-note" />
</x-layouts.admin>
