<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('inventory::general.transfer_orders', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('inventory::general.transfer_orders', 2) }}"
        icon="local_shipping"
        route="inventory.transfer-orders.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-inventory-transfer-orders')
            <x-link href="{{ route('inventory.transfer-orders.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('inventory::general.transfer_orders', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="content">
        @if ($transfer_orders->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\TransferOrder"
                    bulk-action="Modules\Inventory\BulkActions\TransferOrders"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-4/12">
                                <x-slot name="first">
                                    <x-sortablelink column="date" title="{{ trans('general.date') }}" />
                                </x-slot>
                                <x-slot name="second">
                                    <x-sortablelink column="transfer_order_number" title="{{ trans_choice('general.numbers', 1) }}" />
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-4/12 sm:w-2/12">
                                <x-sortablelink column="status" title="{{ trans_choice('general.statuses', 1) }}" />
                            </x-table.th>

                            <x-table.th class="w-4/12">
                                <x-slot name="first">
                                    <x-sortablelink column="source_warehouse_id" title="{{ trans('inventory::transferorders.source') }}" />
                                </x-slot>
                                <x-slot name="second">
                                    <x-sortablelink column="destination_warehouse_id" title="{{ trans('inventory::transferorders.destination') }}" />
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-2/12" kind="right" hidden-mobile>
                                <x-sortablelink column="transfer_order" title="{{ trans_choice('inventory::general.transfer_orders', 1) }}" />
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($transfer_orders as $item)
                            <x-table.tr href="{{ route('inventory.transfer-orders.show', $item->id) }}" data-table-list class="relative flex items-center border-b hover:bg-gray-100 px-1 group">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-4/12">
                                    <x-slot name="first" class="flex items-center font-bold" override="class">
                                        <x-date date="{{ $item->date }}" />
                                    </x-slot>
                                    <x-slot name="second" class="font-normal truncate" override="class">
                                        {{ $item->transfer_order_number }}
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-4/12 sm:w-2/12">
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-xl bg-{{ $item->status_label }} text-text-{{ $item->status_label }}">
                                        {{ trans('inventory::transferorders.' . $item->status) }}
                                    </span>
                                </x-table.td>

                                <x-table.td class="w-4/12">
                                    <x-slot name="first" class="flex items-center font-bold" override="class">
                                        {{ $item->source_warehouse->name }}
                                    </x-slot>
                                    <x-slot name="second" class="font-normal truncate" override="class">
                                        {{ $item->destination_warehouse->name }}
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-2/12" kind="right" hidden-mobile>
                                    {{ $item->transfer_order }}
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$transfer_orders" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page="transfer-orders" docs-category="inventory" title="{{ trans_choice('inventory::general.transfer_orders', 1) }}" />
        @endif
    </x-slot>

    <x-script alias="inventory" file="transfer_orders" />
</x-layouts.admin>
