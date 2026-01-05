<x-show.accordion type="get_paid" :open="($accordionActive === 'make-refund')">
    <x-slot name="head">
        <x-show.accordion.head
            title="{{ $accordion_title }}"
            description="{!! $description !!}"
        />
    </x-slot>

    <x-slot name="body" class="block" override="class">
        <div class="flex flex-wrap space-x-3 rtl:space-x-reverse">
            @if($amount_available)
                <x-button
                    @click="onAddPayment('{{ route('modals.documents.document.transactions.create', $document->id) }}')"
                    id="button-refund"
                    class="px-3 py-1.5 mb-3 sm:mb-0 rounded-lg text-xs font-medium leading-6 bg-green hover:bg-green-700 text-white disabled:bg-green-100"
                    override="class"
                >
                    {{ $button_text }}
                </x-button>
            @endif
        </div>

        <div class="text-xs mt-4" style="margin-left: 0 !important;">
            <span class="font-medium">
                {{ $list_title }} :
            </span>

            @if ($transactions->count())
                @foreach ($transactions as $transaction)
                    <div class="my-2">
                        <span>
                            <x-date :date="$transaction->paid_at" />
                             - {!! trans($refund_translation, [
                                 'amount' => '<span class="font-medium">' . money($transaction->amount, $transaction->currency_code, true) . '</span>',
                                 'account' => '<span class="font-medium">' . $transaction->account->name . '</span>',
                             ]) !!}
                        </span>

                        <div class="flex flex-row">
                            @if ($document->totals->count())
                                <x-button
                                    @click="onEditPayment('{{ route('modals.documents.document.transactions.edit', ['document' => $document->id, 'transaction' => $transaction->id]) }}')"
                                    id="show-slider-actions-transaction-edit-{{ $document->type }}-{{ $transaction->id }}"
                                    class="text-purple mt-1"
                                    override="class"
                                >
                                    <x-button.hover color="to-purple">
                                        {{ trans('general.title.edit', ['type' => trans_choice('general.payments', 1)]) }}
                                    </x-button.hover>
                                </x-button>
                            @endif

                            <span class="mt-1 mr-2 ml-2"> - </span>

                            @php
                                $message = trans('general.delete_confirm', [
                                    'name' => '<strong>' . Date::parse($transaction->paid_at)->format(company_date_format()) . ' - ' . money($transaction->amount, $transaction->currency_code, true) . ' - ' . $transaction->account->name . '</strong>',
                                    'type' => strtolower(trans_choice('general.transactions', 1))
                                ]);
                            @endphp

                            <x-delete-link
                                :model="$transaction"
                                :route="['modals.documents.document.transactions.destroy', $document->id, $transaction->id]"
                                :title="trans('general.title.delete', ['type' => trans_choice('general.payments', 1)])"
                                :message="$message"
                                :label="trans('general.title.delete', ['type' => trans_choice('general.payments', 1)])"
                                class="text-purple mt-1"
                                text-class="bg-no-repeat bg-0-2 bg-0-full hover:bg-full-2 bg-gradient-to-b from-transparent to-purple transition-backgroundSize"
                                override="class"
                            />
                        </div>
                    </div>
                @endforeach
            @else
                <div class="my-2 text-yellow-600 italic">
                    @if ($document->status === 'draft')
                        No se pueden registrar cobros sobre un comprobante en borrador. Por favor, emita el comprobante primero.
                    @else
                        <span>{{ trans('general.no_records') }}</span>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>
</x-show.accordion>
