<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('inventory::general.adjustments', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('inventory::general.adjustments', 2) }}"
        icon="rule"
        route="inventory.adjustments.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-inventory-adjustments')
            <x-link href="{{ route('inventory.adjustments.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('inventory::general.adjustments', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="content">
        @if ($adjustments->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\Adjustment"
                    bulk-action="Modules\Inventory\BulkActions\Adjustments"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-6/12 sm:w-4/12">
                                <x-slot name="first">
                                    {{ trans('general.date') }}
                                </x-slot>
                                <x-slot name="second">
                                    {{ trans_choice('general.numbers', 1) }}
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-6/12 sm:w-4/12">
                                {{ trans_choice('inventory::general.warehouses', 1) }}
                            </x-table.th>

                            <x-table.th class="w-4/12" kind="right" hidden-mobile>
                                {{ trans('inventory::transferorders.reason') }}
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($adjustments as $item)
                            <x-table.tr href="{{ route('inventory.adjustments.show', $item->id) }}" data-table-list class="relative flex items-center border-b hover:bg-gray-100 px-1 group">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-6/12 sm:w-4/12">
                                    <x-slot name="first" class="flex font-bold" override="class">
                                        <x-date date="{{ $item->date }}" />
                                    </x-slot>
                                    <x-slot name="second" class="font-normal" override="class">
                                        {{ $item->adjustment_number }}
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-6/12 sm:w-4/12">
                                    {{ $item->warehouse->name }}
                                </x-table.td>

                                <x-table.td class="w-4/12" kind="right" hidden-mobile>
                                    {{ $item->reason }}
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$adjustments" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page="adjustments" docs-category="inventory" />
        @endif
    </x-slot>

    <x-script alias="inventory" file="adjustments" />
</x-layouts.admin>

