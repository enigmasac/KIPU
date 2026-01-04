<x-show.accordion type="get_paid" :open="false">
    <x-slot name="head">
        <x-show.accordion.head
            title="{{ trans_choice('general.credit_notes', 2) }} {{ trans_choice('general.transactions', 2) }}"
            description="{{ trans('general.no_records') }}"
        />
    </x-slot>

    <x-slot name="body" class="block" override="class">
        <div class="text-xs" style="margin-left: 0 !important;">
            @if ($transactions->count())
                @foreach($transactions as $transaction)
                    <div class="my-4">
                        <span>
                            <x-date :date="$transaction->paid_at" />
                             - {!! trans('documents.transaction', [
                                 'amount' => '<span class="font-medium">' . money($transaction->amount, $transaction->currency_code, true) . '</span>',
                                 'account' => '<span class="font-medium">Credits</span>',
                             ]) !!}
                        </span>

                        <div class="flex flex-row mt-1">
                            @php
                                $message = trans('general.delete_confirm', [
                                    'name' => '<strong>' . Date::parse($transaction->paid_at)->format($date_format) . ' - ' . money($transaction->amount, $transaction->currency_code, true) . '</strong>',
                                    'type' => strtolower(trans_choice('general.transactions', 1))
                                ]);
                            @endphp

                            <x-delete-link
                                :model="$transaction"
                                :route="['credits-transactions.destroy', $transaction->id]"
                                :title="trans('general.title.delete', ['type' => trans_choice('general.payments', 1)])"
                                :message="$message"
                                :label="trans('general.title.delete', ['type' => trans_choice('general.payments', 1)])"
                                class="text-purple"
                                text-class="bg-no-repeat bg-0-2 bg-0-full hover:bg-full-2 bg-gradient-to-b from-transparent to-purple transition-backgroundSize"
                                override="class"
                            />
                        </div>
                    </div>
                @endforeach
            @else
                <div class="my-2">
                    <span>{{ trans('general.no_records') }}</span>
                </div>
            @endif
        </div>
    </x-slot>
</x-show.accordion>
