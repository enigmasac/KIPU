@can('create-sales-credit-notes')
    @if ($invoice->status !== 'draft')
        @php($caption = trans('credit-notes.create_credit_note'))
        @if($amount_exceeded)
            <x-button id="show-more-actions-create-credit-note" class="disabled" disabled title="{{ trans('credit_notes.invoice_amount_is_exceeded') }}">
                {{ $caption }}
            </x-button>
        @else
            <x-link id="show-more-actions-create-credit-note"
                    href="{{ route('sales.credit-notes.create', ['invoice_id' => $invoice->id]) }}"
            >
                {{ $caption }}
            </x-link>
        @endif
    @endif
@endcan
