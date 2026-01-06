<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('composite-items::general.composite_items', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('composite-items::general.composite_items', 2) }}"
        icon="group_work"
        route="composite-items.composite-items.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-composite-items-composite-items')
            <x-link href="{{ route('composite-items.composite-items.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('composite-items::general.composite_items', 1)]) }}
            </x-link>
        @endcan
    </x-slot>
    
    <x-slot name="content">
        @if ($composite_items->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\CompositeItems\Models\CompositeItem"
                    bulk-action="Modules\CompositeItems\BulkActions\CompositeItems"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr class="flex items-center px-1">
                            <x-table.th class="ltr:pr-6 rtl:pl-6 hidden sm:table-cell" override="class">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-6/12">
                                <x-slot name="first">
                                    <x-sortablelink column="name" title="{{ trans('general.name') }}" />
                                </x-slot>
                                <x-slot name="second">
                                    <x-sortablelink column="category.name" title="{{ trans_choice('general.categories', 1) }}" />
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-2/12 hidden sm:table-cell">
                                {{ trans('composite-items::general.estimate_stock') }}
                            </x-table.th>

                            <x-table.th class="w-4/12 ltr:pl-6 rtl:pr-6 py-3 ltr:text-right rtl:text-left text-xs font-medium text-black tracking-wider" override="class">
                                <x-slot name="first">
                                    <x-sortablelink column="sale_price" title="{{ trans('items.sale_price') }}" />
                                </x-slot>
                                <x-slot name="second">
                                    <x-sortablelink column="purchase_price" title="{{ trans('items.purchase_price') }}" />
                                </x-slot>
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($composite_items as $item)                        
                            <x-table.tr href="{{ route('composite-items.composite-items.edit', $item->id) }}" data-table-list class="relative flex items-center border-b hover:bg-gray-100 px-1 group">
                                <x-table.td class="ltr:pr-6 rtl:pl-6 hidden sm:table-cell" override="class">
                                    <x-index.bulkaction.single id="{{ $item->item->id }}" name="{{ $item->item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-6/12 truncate">
                                    <x-slot name="first" class="flex items-center font-bold" override="class">
                                        <div class="truncate">
                                            {{  $item->item->name }}
                                        </div>

                                        @if (! $item->item->enabled)
                                            <x-index.disable text="{{ trans_choice('general.items', 1) }}" />
                                        @endif
                                    </x-slot>
                                    <x-slot name="second" class="font-normal w-32 truncate" override="class">
                                        <div class="flex items-center">
                                            <x-index.category :model="$item->item->category" />
                                        </div>
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-2/12 truncate hidden sm:table-cell">
                                    {{ $item->estimate_stock }}
                                </x-table.td>

                                <x-table.td class="relative w-4/12 ltr:pl-6 rtl:pr-6 py-4 ltr:text-right rtl:text-left whitespace-nowrap text-sm font-normal text-black" override="class">
                                    <x-slot name="first">
                                        @if ($item->item->sale_price)
                                            <x-money amount="{{ $item->item->sale_price }}" currency="{{ setting('default.currency') }}" convert />
                                        @else
                                            <x-empty-data />
                                        @endif
                                    </x-slot>
                                    <x-slot name="second">
                                        @if ($item->item->purchase_price)
                                            <x-money amount="{{ $item->item->purchase_price }}" currency="{{ setting('default.currency') }}" convert />
                                        @else
                                            <x-empty-data />
                                        @endif
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="p-0" override="class">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$composite_items" />
            </x-index.container>
        @else
            <x-empty-page group="composite-items" page="composite-items" docs-category="inventory" hide-button-import />
        @endif
    </x-slot>

    <x-script alias="composite-items" file="composite-items" />
</x-layouts.admin>