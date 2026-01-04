<x-layouts.print>
    <x-slot name="title">
        {{ trans_choice('general.debit_notes', 1).': '.$debit_note->document_number }}
    </x-slot>

    <x-slot name="content">
        <x-documents.template.ddefault
            type="debit-note"
            :document="$debit_note"
            hide-due-at
        />
    </x-slot>
</x-layouts.print>
