<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('inventory::general.warehouses', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('inventory::general.warehouses', 2) }}"
        icon="warehouses"
        route="inventory.warehouses.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-inventory-warehouses')
            <x-link href="{{ route('inventory.warehouses.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('inventory::general.warehouses', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @can('create-inventory-warehouses')
                <x-dropdown.link href="{{ route('import.create', ['inventory', 'warehouses']) }}">
                    {{ trans('import.import') }}
                </x-dropdown.link>
            @endcan

            <x-dropdown.link href="{{ route('inventory.warehouses.export', request()->input()) }}">
                {{ trans('general.export') }}
            </x-dropdown.link>
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        @if ($warehouses->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\Warehouse"
                    bulk-action="Modules\Inventory\BulkActions\Warehouses"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-6/12 sm:w-5/12">
                                <x-sortablelink column="name" title="{{ trans('general.name') }}" />
                            </x-table.th>

                            <x-table.th class="w-3/12" hidden-mobile>
                                <x-sortablelink column="category.name" title="{{ trans('general.email') }}" />
                            </x-table.th>

                            <x-table.th class="w-2/12" hidden-mobile>
                                <x-sortablelink column="phone" title="{{ trans('general.phone') }}" />
                            </x-table.th>

                            <x-table.th class="w-6/12 sm:w-2/12" kind="right">
                                {{ trans('inventory::warehouses.total_item') }}
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($warehouses as $item)
                            @php
                                $tooltip = '';

                                if ($item->id == setting('inventory.default_warehouse')) {
                                    $tooltip = 'hidden';
                                }
                            @endphp

                            <x-table.tr href="{{ route('inventory.warehouses.show', $item->id) }}">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-6/12 sm:w-5/12">
                                    <x-slot name="first" class="flex">
                                        {{ $item->name }}
                                    
                                        @if (setting('inventory.default_warehouse') == $item->id)
                                            <x-index.default text="{{ trans('inventory::warehouses.default') }}" />
                                        @endif

                                        @if (! $item->enabled)
                                            <x-index.disable text="{{ trans_choice('inventory::general.warehouses', 1) }}" />
                                        @endif
                                    </x-slot>
                                </x-table.td>

                                <x-table.td class="w-3/12" hidden-mobile>
                                    {{ !empty($item->email) ? $item->email : trans('general.na') }}
                                </x-table.td>

                                <x-table.td class="w-2/12" hidden-mobile>
                                    {{ $item->phone }}
                                </x-table.td>

                                <x-table.td class="w-6/12 sm:w-2/12" kind="right">
                                    {{ $item->items->count() }}
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$warehouses" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page="warehouses" docs-category="inventory" />
        @endif
    </x-slot>

    <x-script alias="inventory" file="warehouses" />
</x-layouts.admin>
