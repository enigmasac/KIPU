<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('inventory::general.variants', 2) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans_choice('inventory::general.variants', 2) }}"
        icon="tune"
        route="inventory.variants.index"
    ></x-slot>

    <x-slot name="buttons">
        @can('create-inventory-variants')
            <x-link href="{{ route('inventory.variants.create') }}" kind="primary">
                {{ trans('general.title.new', ['type' => trans_choice('inventory::general.variants', 1)]) }}
            </x-link>
        @endcan
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @can('create-inventory-variants')
                <x-dropdown.link href="{{ route('import.create', ['inventory', 'variants']) }}">
                    {{ trans('import.import') }}
                </x-dropdown.link>
            @endcan

            <x-dropdown.link href="{{ route('inventory.variants.export', request()->input()) }}">
                {{ trans('general.export') }}
            </x-dropdown.link>
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        @if ($variants->count() || request()->get('search', false))
            <x-index.container>
                <x-index.search
                    search-string="Modules\Inventory\Models\Variant"
                    bulk-action="Modules\Inventory\BulkActions\Variants"
                />

                <x-table>
                    <x-table.thead>
                        <x-table.tr>
                            <x-table.th kind="bulkaction">
                                <x-index.bulkaction.all />
                            </x-table.th>

                            <x-table.th class="w-10/12">
                                <x-sortablelink column="name" title="{{  trans('general.name') }}" />
                            </x-table.th>

                            <x-table.th class="w-2/12" kind="right">
                                <x-sortablelink column="values" title="{{ trans_choice('inventory::variants.values', 2) }}" />
                            </x-table.th>
                        </x-table.tr>
                    </x-table.thead>

                    <x-table.tbody>
                        @foreach($variants as $item)
                            <x-table.tr href="{{ route('inventory.variants.edit', $item->id) }}">
                                <x-table.td kind="bulkaction">
                                    <x-index.bulkaction.single id="{{ $item->id }}" name="{{ $item->name }}" />
                                </x-table.td>

                                <x-table.td class="w-10/12">
                                    {{ $item->name }}

                                    @if (! $item->enabled)
                                        <x-index.disable text="{{ trans_choice('inventory::general.variants', 1) }}" />
                                    @endif
                                </x-table.td>

                                <x-table.td class="w-2/12" kind="right">
                                    {{ $item->values->count() }}
                                </x-table.td>

                                <x-table.td kind="action">
                                    <x-table.actions :model="$item" />
                                </x-table.td>
                            </x-table.tr>
                        @endforeach
                    </x-table.tbody>
                </x-table>

                <x-pagination :items="$variants" />
            </x-index.container>
        @else
            <x-empty-page group="inventory" page="variants" docs-category="inventory" />
        @endif
    </x-slot>

    <x-script alias="inventory" file="variants" />
</x-layouts.admin>
