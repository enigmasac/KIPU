<x-table>
    <x-table.thead>
        <x-table.tr>
            @if (! $hideBulkAction)
            <x-table.th class="{{ $classBulkAction }}" override="class">
                <x-index.bulkaction.all />
            </x-table.th>
            @endif

            @stack('due_at_and_issued_at_th_start')
            @if (! $hideDueAt || ! $hideIssuedAt)
            <x-table.th class="{{ $classDueAtAndIssueAt }}">
                @stack('due_at_th_start')
                @if (! $hideDueAt)
                <x-slot name="first">
                    @stack('due_at_th_inside_start')
                    <x-sortablelink column="due_at" title="{{ trans($textDueAt) }}" />
                    @stack('due_at_th_inside_end')
                </x-slot>
                @endif
                @stack('due_at_th_end')

                @stack('issued_at_th_start')
                @if (! $hideIssuedAt)
                <x-slot name="second">
                    @stack('issued_at_th_inside_start')
                    <x-sortablelink column="issued_at" title="{{ trans($textIssuedAt) }}" />
                    @stack('issued_at_th_inside_end')
                </x-slot>
                @endif
                @stack('issued_at_th_end')
            </x-table.th>
            @endif
            @stack('due_at_and_issued_at_th_end')

            @stack('status_th_start')
            @if (! $hideStatus)
            <x-table.th class="{{ $classStatus }}">
                @stack('status_th_inside_start')
                <x-sortablelink column="status" title="{{ trans_choice('general.statuses', 1) }}" />
                @stack('status_th_inside_end')
            </x-table.th>
            @endif
            @stack('status_th_end')

            <x-table.th class="w-20 text-center table-title">
                <span class="text-xs text-gray-500 leading-none font-semibold">Notas</span>
            </x-table.th>

            <x-table.th class="w-2/12 text-center table-title">
                <span class="text-xs text-gray-500 leading-none font-semibold uppercase tracking-wide">SUNAT</span>
            </x-table.th>

            @stack('contact_name_ane_document_number_th_start')
            @if (! $hideContactName || ! $hideDocumentNumber)
            <x-table.th class="{{ $classContactNameAndDocumentNumber }}">
                @stack('contact_name_th_start')
                @if (! $hideContactName)
                <x-slot name="first">
                    @stack('contact_name_th_inside_start')
                    <x-sortablelink column="contact_name" title="{{ trans_choice($textContactName, 1) }}" />
                    @stack('contact_name_th_inside_end')
                </x-slot>
                @endif
                @stack('contact_name_th_end')

                @stack('document_number_th_start')
                @if (! $hideDocumentNumber)
                <x-slot name="second">
                    @stack('document_number_th_inside_start')
                    <x-sortablelink column="document_number" title="{{ trans_choice($textDocumentNumber, 1) }}" />
                    @stack('document_number_th_inside_end')
                </x-slot>
                @endif
                @stack('document_number_th_end')
            </x-table.th>
            @endif
            @stack('contact_name_ane_document_number_th_end')

            @stack('amount_th_start')
            @if (! $hideAmount)
            <x-table.th class="{{ $classAmount }}" kind="amount">
                @stack('amount_th_inside_start')
                <x-sortablelink column="amount" title="{{ trans('general.amount') }}" />
                @stack('amount_th_inside_end')
            </x-table.th>
            @endif
            @stack('amount_th_end')
        </x-table.tr>
    </x-table.thead>

    <x-table.tbody>
        @foreach($documents as $item)
            @php
                $paid = $item->paid;
                $credit_note_label = null;
                $credit_note_class = '';
                $credit_notes_total = 0;
                $debit_note_label = null;
                $debit_note_class = '';
                $debit_notes_total = 0;

                    if ($type === 'invoice' && $item->relationLoaded('credit_notes')) {
                    $credit_notes = $item->credit_notes->reject(function ($credit_note) {
                        return $credit_note->status === 'cancelled'
                            || strtolower((string) $credit_note->sunat_status) === 'rechazado';
                    });
                    $precision = currency($item->currency_code)->getPrecision();
                    $credit_notes_total = round($credit_notes->sum('amount'), $precision);

                        if ($credit_notes_total > 0) {
                            $invoice_amount = round($item->amount, $precision);
                            $is_full_credit = bccomp((string) $credit_notes_total, (string) $invoice_amount, $precision) >= 0;
                            $credit_note_label = $is_full_credit ? 'Anulada por N.C.' : 'N.C. parcial';
                            $credit_note_class = $is_full_credit ? 'text-red-500' : 'text-yellow-500';
                        }
                    }

                    if ($type === 'invoice' && $item->relationLoaded('debit_notes')) {
                    $debit_notes = $item->debit_notes->reject(function ($debit_note) {
                        return $debit_note->status === 'cancelled'
                            || strtolower((string) $debit_note->sunat_status) === 'rechazado';
                    });
                    $precision = $precision ?? currency($item->currency_code)->getPrecision();
                    $debit_notes_total = round($debit_notes->sum('amount'), $precision);

                    if ($debit_notes_total > 0) {
                        $debit_note_label = $debit_notes_total >= round($item->amount, $precision)
                            ? 'N.D. total'
                            : 'N.D. parcial';
                        $debit_note_class = 'text-green-600';
                    }
                }

                $credit_tooltip = null;
                $credit_icon_class = 'bg-yellow-500 text-white';
                $credit_badge_class = 'border-yellow-500 text-yellow-500';
                if ($credit_notes_total > 0) {
                    $credit_tooltip = 'Afecto a nota de crédito ' .
                        ($is_full_credit ? 'total' : 'parcial') .
                        ': -' . money($credit_notes_total, $item->currency_code, true)->format();
                    if ($is_full_credit) {
                        $credit_icon_class = 'bg-red-500 text-white';
                        $credit_badge_class = 'border-red-500 text-red-500';
                    }
                }

                $debit_tooltip = null;
                if ($debit_notes_total > 0) {
                    $debit_tooltip = 'Afecto a nota de débito: +' .
                        money($debit_notes_total, $item->currency_code, true)->format();
                }
            @endphp
            <x-table.tr href="{{ route($showRoute, $item->id) }}">
                @if (! $hideBulkAction)
                <x-table.td class="{{ $classBulkAction }}" override="class">
                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->document_number }}" />
                </x-table.td>
                @endif

                @stack('due_at_and_issued_at_td_start')
                @if (! $hideDueAt || ! $hideIssuedAt)
                <x-table.td class="{{ $classDueAtAndIssueAt }}">
                    @stack('due_at_td_start')
                    @if (! $hideDueAt)
                    <x-slot name="first" class="font-bold" override="class">
                        @stack('due_at_td_inside_start')
                        <x-date :date="$item->due_at" function="diffForHumans" />
                        @stack('due_at_td_inside_end')
                    </x-slot>
                    @endif
                    @stack('due_at_td_end')

                    @stack('issued_at_td_start')
                    @if (! $hideIssuedAt)
                    <x-slot name="second">
                        @stack('issued_at_td_inside_start')
                        <x-date date="{{ $item->issued_at }}" />
                        @stack('issued_at_td_inside_end')
                    </x-slot>
                    @endif
                    @stack('issued_at_td_end')
                </x-table.td>
                @endif
                @stack('due_at_and_issued_at_td_end')

                @stack('status_td_start')
                @if (!$hideStatus)
                    <x-table.td class="{{ $classStatus }}">
                        @stack('status_td_inside_start')
                        <x-show.status status="{{ $item->status }}" background-color="bg-{{ $item->status_label }}" text-color="text-text-{{ $item->status_label }}" />
                        @stack('status_td_inside_end')
                    </x-table.td>
                @endif
                @stack('status_td_end')

                <x-table.td class="w-20 sm:w-32 text-center">
                <div class="flex items-center justify-center gap-3">
                    @if ($credit_note_label)
                        <x-tooltip :id="'tooltip-note-credit-' . $item->id" placement="top" :message="$credit_tooltip ?? $credit_note_label">
                            <span class="relative inline-flex items-center justify-center">
                                <span class="material-icons-outlined rounded-full p-1 {{ $credit_icon_class }}" role="img" aria-label="{{ $credit_tooltip ?? $credit_note_label }}">
                                    receipt_long
                                </span>
                                <span class="absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center text-[10px] font-bold leading-none {{ $credit_badge_class }} rounded-full bg-white">
                                    -
                                </span>
                            </span>
                        </x-tooltip>
                    @endif

                    @if ($debit_note_label)
                        <x-tooltip :id="'tooltip-note-debit-' . $item->id" placement="top" :message="$debit_tooltip ?? $debit_note_label">
                            <span class="relative inline-flex items-center justify-center">
                                <span class="material-icons-outlined rounded-full p-1 bg-green-600 text-white" role="img" aria-label="{{ $debit_tooltip ?? $debit_note_label }}">
                                    receipt_long
                                </span>
                                <span class="absolute -top-1 -right-1 w-4 h-4 flex items-center justify-center text-[10px] font-bold leading-none border border-green-600 rounded-full bg-white text-green-600">
                                    +
                                </span>
                            </span>
                        </x-tooltip>
                    @endif

                    @if (!$credit_note_label && ! $debit_note_label)
                        <span class="text-gray-300 text-xs">—</span>
                    @endif
                </div>
                </x-table.td>

                <x-table.td class="w-2/12 text-center">
                    <span class="px-2 py-1 rounded-md text-xs {{ $item->sunat_status == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($item->sunat_status ?? 'pendiente') }}
                    </span>
                </x-table.td>

                @stack('contact_name_and_document_number_td_start')
                @if (! $hideContactName || ! $hideDocumentNumber)
                <x-table.td class="{{ $classContactNameAndDocumentNumber }}">
                    @stack('contact_name_td_start')
                    @if (! $hideContactName)
                    <x-slot name="first">
                        @stack('contact_name_td_inside_start')
                        {{ $item->contact_name }}
                        @stack('contact_name_td_inside_end')
                    </x-slot>
                    @endif
                    @stack('contact_name_td_end')

                    @stack('document_number_td_start')
                    @if (! $hideDocumentNumber)
                    <x-slot name="second" class="w-20 group" data-tooltip-target="tooltip-information-{{ $item->id }}" data-tooltip-placement="left" override="class">
                        @stack('document_number_td_inside_start')
                        <span class="border-black border-b border-dashed">
                            {{ $item->document_number }}
                        </span>

                        <div class="w-28 absolute h-10 -ml-12 -mt-6"></div>
                        @stack('document_number_td_inside_end')

                        <x-documents.index.information :document="$item" :hide-show="$hideShow" :show-route="$showContactRoute" />
                    </x-slot>
                    @endif
                    @stack('document_number_td_end')
                </x-table.td>
                @endif
                @stack('contact_name_and_document_number_td_end')

                @stack('amount_td_start')
                @if (! $hideAmount)
                    <x-table.td class="{{ $classAmount }}" kind="amount">
                        @stack('amount_td_inside_start')
                        @php
                            $display_amount = ($type === 'invoice') ? $item->amount_due : $item->amount;
                        @endphp
                        <div class="flex items-center justify-end">
                            <x-money :amount="$display_amount" :currency="$item->currency_code" />
                        </div>
                        @stack('amount_td_inside_end')
                    </x-table.td>

                <x-table.td kind="action">
                    <x-table.actions :model="$item" />
                </x-table.td>
                @endif
                @stack('amount_td_end')
            </x-table.tr>
        @endforeach
    </x-table.tbody>
</x-table>

<x-pagination :items="$documents" />
