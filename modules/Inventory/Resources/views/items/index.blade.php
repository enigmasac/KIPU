<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('general.items', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('general.items', 2) }}"
        icon="inventory_2"
        route="inventory.items.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-common-items')
            <x-link href="{{ route('inventory.items.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('general.items', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @can('create-common-items')
                <x-dropdown.link href="{{ route('import.create', ['inventory', 'items']) }}">
                    {{ trans('import.import') }}
                </x-dropdown.link>
            @endcan

            <x-dropdown.link href="{{ route('inventory.items.export', array_merge(['company_id' => company_id()], request()->input())) }}">
                {{ trans('general.export') }}
            </x-dropdown.link>
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        @if ($items->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\Common\Item"
                    bulk-action="Modules\Inventory\BulkActions\Items"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-5/12 sm:w-6/12">
                                <x-slot name="first">
                                    <x-sortablelink column="name" title="{{ trans('general.name') }}" />
                                </x-slot>
                                <x-slot name="second">
                                    <x-sortablelink column="description" title="{{ trans('general.description') }}" />
                                </x-slot>
                            </x-table.th>

                            <x-table.th class="w-3/12" hidden-mobile>
                                <x-sortablelink column="category.name" title="{{ trans_choice('general.categories', 1) }}" />
                            </x-table.th>

                            <x-table.th class="w-3/12 sm:w-2/12">
                                {{ trans('inventory::general.stock') }}
                            </x-table.th>

                            <x-table.th class="w-/12 sm:w-3/12" kind="amount">
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
                        @foreach($items as $item)
                            @php
                                $action = route('inventory.items.edit', $item->id);
                                $more_actions = [
                                    [
                                        'title' => trans('general.edit'),
                                        'icon' => 'edit',
                                        'url' => route('inventory.items.edit', $item->id),
                                        'permission' => 'update-inventory-items',
                                    ],
                                    [
                                        'title' => trans('general.duplicate'),
                                        'icon' => 'file_copy',
                                        'url' => route('items.duplicate', $item->id),
                                        'permission' => 'create-common-items',
                                    ],
                                    [
                                        'type' => 'delete',
                                        'title' => trans('general.delete'),
                                        'icon' => 'delete',
                                        'route' => 'inventory.items.destroy',
                                        'permission' => 'delete-inventory-items',
                                        'model' => $item,
                                    ],
                                ];

                                if ($item->inventories->count()) {
                                    $action = route('inventory.items.show', $item->id);
                                    $more_actions = $item->inventories[0]->line_actions;
                                }  
                            @endphp
                            
                            <x-table.tr href="{{ $action }}">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-5/12 sm:w-6/12">
                                    <x-slot name="first" class="flex font-bold" override="class">
                                        {{ $item->name }}

                                        @if (! $item->enabled)
                                            <x-index.disable text="{{ trans_choice('general.items', 1) }}" />
                                        @endif
                                    </x-slot>
                                    <x-slot name="second" class="font-normal" override="class">
                                        {{ $item->description }}
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-3/12" hidden-mobile>
                                    <div class="flex items-center">
                                        <x-index.category :model="$item->category" />
                                    </div>
                                </x-table.td>

                                <x-table.td class="w-3/12 sm:w-2/12">
                                    @if ($item->inventories->count())
                                        {{ ($item->inventories->sum('opening_stock')) ? $item->inventories->sum('opening_stock') : 0 }}
                                    @else
                                        {{ trans('general.na') }}
                                    @endif

                                    @if ($item->inventories->avg('reorder_level') > $item->inventories->sum('opening_stock'))
                                        <x-tooltip id="tooltip-{{ $item->id }}" placement="top" message="{{ trans('inventory::items.low_stock') }}" backgroundColor="bg-white" textColor="text-black" borderColor="before:bg-purple">
                                            <span class="material-icons-round text-red text-xs ml-2">warning</span>
                                        </x-tooltip>
                                    @endif
                                </x-table.td>

                                <x-table.td class="w-4/12 sm:w-3/12" kind="amount">
                                    <x-slot name="first">
                                        @if ($item->sale_price)
                                            <x-money :amount="$item->sale_price" :currency="setting('default.currency')" convert />
                                        @else
                                            <x-empty-data />
                                        @endif
                                    </x-slot>
                                    <x-slot name="second">
                                        @if ($item->purchase_price)
                                            <x-money :amount="$item->purchase_price" :currency="setting('default.currency')" convert />
                                        @else
                                            <x-empty-data />
                                        @endif
                                    </x-slot>
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :actions="$more_actions" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$items" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page="items" docs-category="inventory" />
        @endif
    </x-slot>

    <x-script alias="inventory" file="items" />
</x-layouts.admin>