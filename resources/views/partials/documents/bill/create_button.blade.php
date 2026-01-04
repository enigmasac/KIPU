@can('create-purchases-debit-notes')
    @if ($bill->status !== 'draft')
        @php($caption = trans('general.title.create', ['type' => trans_choice('general.debit_notes', 1)]))
        @if($amount_exceeded)
            <x-button id="show-more-actions-create-debit-note" class="disabled" disabled title="{{ trans('debit_notes.bill_amount_is_exceeded') }}">
                {{ $caption }}
            </x-button>
        @else
            <x-link id="show-more-actions-create-debit-note"
                    href="{{ route('purchases.debit-notes.create', ['bill_id' => $bill->id]) }}"
            >
                {{ $caption }}
            </x-link>
        @endif
    @endif
@endcan
