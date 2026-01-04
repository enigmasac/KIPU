<x-layouts.admin>
    <x-slot name="title">
        {{ trans_choice('general.debit_notes', 1) . ': ' . $debit_note->document_number }}
    </x-slot>

    <x-slot name="status">
        <x-show.status status="{{ $debit_note->status }}"
                       background-color="bg-{{ $debit_note->status_label }}"
                       text-color="text-text-{{ $debit_note->status_label }}"
        />
    </x-slot>

    <x-slot name="buttons">
        <x-documents.show.buttons
            type="debit-note"
            :document="$debit_note"
            permission-create="create-sales-credit-notes"
            permission-update="update-sales-credit-notes"
            text-create="{{ trans('general.new') . ' ' . trans_choice('general.debit_notes', 1) }}"
        />
    </x-slot>

    <x-slot name="moreButtons">
        <x-documents.show.more-buttons type="debit-note" :document="$debit_note" />
    </x-slot>

    <x-slot name="content">
        <x-documents.show.content
            type="debit-note"
            :document="$debit_note"
            hide-receive
            hide-make-payment
            hide-schedule
            hide-children
            hide-due-at
            hide-header-due-at
            hide-button-received
            hide-button-share
            hide-email
            hide-get-paid
            permission-update="update-sales-credit-notes"
        />
    </x-slot>

    @push('stylesheet')
        <link rel="stylesheet" href="{{ asset('public/css/print.css?v=' . version('short')) }}" type="text/css">
    @endpush

    <x-documents.script type="debit-note" />
</x-layouts.admin>
