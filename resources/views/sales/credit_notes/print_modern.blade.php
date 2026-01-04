<x-layouts.print>
    <x-slot name="title">
        {{ trans_choice('general.credit_notes', 1).': '.$credit_note->document_number }}
    </x-slot>

    <x-slot name="content">
        <x-documents.template.modern
            type="credit-note"
            :document="$credit_note"
            hide-due-at
        />
    </x-slot>
</x-layouts.print>
