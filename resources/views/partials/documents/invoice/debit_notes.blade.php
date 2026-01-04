@php
    $precision = currency($invoice->currency_code)->getPrecision();
    $debit_notes_total = round($debit_notes_total ?? $debit_notes->sum('amount'), $precision);
    $invoice_amount = round($invoice->amount, $precision);
    $is_full_debit = bccomp((string) $debit_notes_total, (string) $invoice_amount, $precision) >= 0;
    $debit_status = $is_full_debit ? 'N.D. total' : 'N.D. parcial';
    $debit_total_label = money($debit_notes_total, $invoice->currency_code, true)->format();
@endphp

<x-show.accordion type="debit_notes" :open="false">
    <x-slot name="head">
        <x-show.accordion.head
            title="{{ trans_choice('general.debit_notes', 2) }}"
            description="{{ $debit_status }} · Total: {{ $debit_total_label }}"
        />
    </x-slot>

    <x-slot name="body" class="block" override="class">
        <div class="text-xs" style="margin-left: 0 !important;">
            @foreach ($debit_notes as $debit_note)
                <div class="flex items-center justify-between my-3">
                    <div class="flex flex-col">
                        <x-link href="{{ route('sales.debit-notes.show', $debit_note->id) }}" class="font-medium border-b border-black" override="class">
                            {{ $debit_note->document_number }}
                        </x-link>
                        <span class="text-gray-500">
                            <x-date :date="$debit_note->issued_at" />
                        </span>
                    </div>

                    <div class="font-medium text-blue-500">
                        <x-money :amount="$debit_note->amount" :currency="$debit_note->currency_code" />
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>
</x-show.accordion>
