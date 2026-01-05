@if ($invoice->status !== 'draft')
    <div class="mr-auto">
        <x-dropdown id="dropdown-emitir-nota-v6">
            <x-slot name="trigger" class="flex items-center px-3 py-1.5 rounded-xl text-sm font-medium leading-6 bg-purple hover:bg-purple-700 text-white shadow-sm cursor-pointer whitespace-nowrap transition-all" override="class">
                <span>Emitir Nota</span>
                <span class="material-icons text-xs ml-1">expand_more</span>
            </x-slot>

            @can('create-sales-credit-notes')
                @if($amount_exceeded)
                    <x-dropdown.link href="#" class="opacity-50 cursor-not-allowed text-xs" title="{{ trans('credit_notes.invoice_amount_is_exceeded') }}">
                        <div class="flex items-center min-w-[180px]">
                            <span class="material-icons-outlined text-sm mr-2 text-red-500">remove_circle_outline</span>
                            <span>{{ trans('credit-notes.create_credit_note') }}</span>
                        </div>
                    </x-dropdown.link>
                @else
                    <x-dropdown.link href="{{ route('sales.credit-notes.create', ['invoice_id' => $invoice->id]) }}">
                        <div class="flex items-center min-w-[180px]">
                            <span class="material-icons-outlined text-sm mr-2 text-red-500">remove_circle_outline</span>
                            <span>{{ trans('credit-notes.create_credit_note') }}</span>
                        </div>
                    </x-dropdown.link>
                @endif
            @endcan

            @canany(['create-sales-debit-notes', 'create-sales-credit-notes'])
                <x-dropdown.link href="{{ route('sales.debit-notes.create', ['invoice_id' => $invoice->id]) }}" class="border-t">
                    <div class="flex items-center min-w-[180px]">
                        <span class="material-icons-outlined text-sm mr-2 text-green-600">add_circle_outline</span>
                        <span>{{ trans('debit-notes.create_debit_note') }}</span>
                    </div>
                </x-dropdown.link>
            @endcanany
        </x-dropdown>
    </div>
@endif