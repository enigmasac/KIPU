@canany(['create-sales-debit-notes', 'create-sales-credit-notes'])
    @if ($invoice->status !== 'draft')
        <x-link id="show-more-actions-create-debit-note"
                href="{{ route('sales.debit-notes.create', ['invoice_id' => $invoice->id]) }}"
                class="ml-2"
        >
            {{ trans('debit-notes.create_debit_note') }}
        </x-link>
    @endif
@endcan
