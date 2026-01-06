<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('inventory::general.item_groups', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('inventory::general.item_groups', 2) }}"
        icon="ballot"
        route="inventory.item-groups.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-inventory-item-groups')
            <x-link href="{{ route('inventory.item-groups.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('inventory::general.item_groups', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @can('create-inventory-item-groups')
                <x-dropdown.link href="{{ route('import.create', ['inventory', 'item-groups']) }}">
                    {{ trans('import.import') }}
                </x-dropdown.link>
            @endcan

            <x-dropdown.link href="{{ route('inventory.item-groups.export', request()->input()) }}">
                {{ trans('general.export') }}
            </x-dropdown.link>
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        @if ($item_groups->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\ItemGroup"
                    bulk-action="Modules\Inventory\BulkActions\ItemGroups"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-7/12">
                                <x-sortablelink column="name" title="{{ trans('general.name') }}" />
                            </x-table.th>

                            <x-table.th class="w-3/12"  hidden-mobile>
                                <x-sortablelink column="category.name" title="{{ trans_choice('general.categories', 1) }}" />
                            </x-table.th>

                             <x-table.th class="w-5/12 sm:w-2/12" kind="right">
                                {{ trans_choice('general.items', 2) }}
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($item_groups as $item)
                            <x-table.tr href="{{ route('inventory.item-groups.edit', $item->id) }}">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-7/12">
                                    {{ $item->name }}

                                    @if (! $item->enabled)
                                        <x-index.disable text="{{ trans_choice('inventory::general.item_groups', 1) }}" />
                                    @endif
                                </x-table.td>

                                <x-table.td class="w-3/12" hidden-mobile>
                                    <div class="flex items-center">
                                        <x-index.category :model="$item->category" />
                                    </div>
                                </x-table.td>

                                <x-table.td class="w-5/12 sm:w-2/12" kind="right">
                                    {{ $item->items->count() }}
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$item_groups" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page="item-groups" docs-category="inventory" />
        @endif
    </x-slot>

    <x-script alias="inventory" file="item_groups" />
</x-layouts.admin>