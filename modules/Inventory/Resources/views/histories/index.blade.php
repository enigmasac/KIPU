<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('inventory::general.histories', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('inventory::general.histories', 2) }}"
        icon="history"
        route="inventory.histories.index"
    ></x-slot>

    <x-slot name="content">
        @if ($histories->count())
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\History"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th class="w-4/12 sm:w-2/12">
                                <x-sortablelink column="created_at" title="{{ trans('general.date') }}" />
                            </x-table.th>

                            <x-table.th class="w-6/12" hidden-mobile>
                                <x-slot name="first">
                                    <x-sortablelink column="item.name" title="{{ trans_choice('general.items', 1) }}" />
                                </x-slot>
                                <x-slot name="second">
                                    <x-sortablelink column="warehouse.name" title="{{ trans_choice('inventory::general.warehouses', 1) }}" />
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-4/12 sm:w-2/12">
                                <x-sortablelink column="type_type" title="{{ trans('inventory::general.action') . ' ' . trans_choice('general.types', 1) }}" />
                            </x-table.th>

                            <x-table.th class="w-4/12 sm:w-2/12" kind="right">
                                <x-sortablelink column="quantity" title="{{ trans('inventory::general.stock') }}" />
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($histories as $item)
                            <x-table.tr>
                                <x-table.td class="w-4/12 sm:w-2/12">
                                    <x-date date="{{ $item->created_at }}" />
                                </x-table.td>

                                <x-table.td class="w-6/12" hidden-mobile>
                                    <x-slot name="first" class="flex font-bold" override="class">
                                        <x-link href="{{ route('inventory.items.show', $item->item_id) }}" class="font-bold truncate border-b border-black border-dashed" override="class">
                                            {{ $item->item->name }}
                                        </x-link>
                                    </x-slot>
                                    <x-slot name="second" class="font-normal" override="class">
                                        <x-link href="{{ route('inventory.warehouses.show', $item->warehouse_id) }}" class="font-normal truncate border-b border-black border-dashed" override="class">
                                            {{ $item->warehouse->name }}
                                        </x-link>
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-4/12 sm:w-2/12">
                                    <x-link href="{{ url($item->action_url) }}" class="font-normal truncate border-b border-black border-dashed" override="class">
                                        {{ $item->action_type }}
                                    </x-link>
                                </x-table.td>

                                <x-table.td class="w-4/12 sm:w-2/12" kind="right">
                                    {{ $item->quantity }}
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$histories" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page='histories' docs-category='inventory' hide-button-create hide-button-import />
        @endif
    </x-slot>

    <x-script alias="inventory" file="histories" />
</x-layouts.admin>

