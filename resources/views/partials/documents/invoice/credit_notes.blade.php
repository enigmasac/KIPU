@php
    $precision = currency($invoice->currency_code)->getPrecision();
    $credit_notes_total = round($credit_notes_total ?? $credit_notes->sum('amount'), $precision);
    $invoice_amount = round($invoice->amount, $precision);
    $is_full_credit = bccomp((string) $credit_notes_total, (string) $invoice_amount, $precision) >= 0;
    $credit_status = $is_full_credit ? 'Anulada por N.C.' : 'N.C. parcial';
    $credit_total_label = money($credit_notes_total, $invoice->currency_code, true)->format();
@endphp

<x-show.accordion type="credit_notes" :open="false">
    <x-slot name="head">
        <x-show.accordion.head
            title="{{ trans_choice('general.credit_notes', 2) }}"
            description="{{ $credit_status }} - Total: {{ $credit_total_label }}"
        />
    </x-slot>

    <x-slot name="body" class="block" override="class">
        <div class="text-xs" style="margin-left: 0 !important;">
            @foreach ($credit_notes as $credit_note)
                <div class="flex items-center justify-between my-3">
                    <div class="flex flex-col">
                        <x-link href="{{ route('sales.credit-notes.show', $credit_note->id) }}" class="font-medium border-b border-black" override="class">
                            {{ $credit_note->document_number }}
                        </x-link>
                        <span class="text-gray-500">
                            <x-date :date="$credit_note->issued_at" />
                        </span>
                    </div>

                    <div class="font-medium">
                        <x-money :amount="$credit_note->amount" :currency="$credit_note->currency_code" />
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>
</x-show.accordion>
