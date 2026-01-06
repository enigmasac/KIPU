<x-layouts.admin>
    <x-slot name="title">
        {{ $item->name }}
    </x-slot>

    <x-slot name="status">
        @if (! $item->enabled)
            <x-index.disable text="{{ trans_choice('general.items', 1) }}" />
        @endif
    </x-slot>

    <x-slot name="favorite"
        title="{{ $item->name }}"
        icon="inventory_2"
        :route="['inventory.items.show', $item->id]"
    ></x-slot>

    <x-slot name="buttons">
        @can('update-inventory-items')
            <x-link href="{{ route('inventory.items.edit', $item->id) }}">
                {{ trans('general.edit') }}
            </x-link>

            <x-dropdown id="item-show">
                <x-slot name="trigger">
                    <span class="material-icons">more_horiz</span>
                </x-slot>
            
                @can('create-common-items')
                    <x-dropdown.link href="{{ route('items.duplicate', $item->id) }}">
                        {{ trans('general.duplicate') }}
                    </x-dropdown.link>
                @endcan
            
                <x-dropdown.link href="{{ route('inventory.items.export-history', $item->id, request()->input()) }}">
                    {{ trans('general.export') }}
                </x-dropdown.link>

                @if ($item->inventory()->value('barcode'))
                    <x-dropdown.link href="{{ route('inventory.items.print-barcode', $item->id) }}">
                        {{ trans('inventory::general.print_barcode') }}
                    </x-dropdown.link>
                @else
                    <x-dropdown.link href="{{ route('inventory.items.generate-barcode', $item->id) }}">
                        {{ trans('inventory::general.generate_barcode') }}
                    </x-dropdown.link>
                @endif
                
                @can('delete-inventory-items')
                    <x-delete-link :model="$item" route="inventory.items.destroy" :text="trans_choice('inventory::general.items', 1)" />
                @endcan
            </x-dropdown>
        @endcan
    </x-slot>

    <x-slot name="content">
        <x-show.container>
            <x-show.summary>
                <x-show.summary.left>
                    @stack('item_information_start')
                        @if ($item->picture)
                            <div class="w-full lg:w-5/12 flex items-center">
                                <div class="flex flex-col text-black text-sm font-medium">
                                    <img class="text-sm font-weight-600 max-w" src="{{ Storage::url($item->picture->id) }}" alt="{{ $item->name }}">
                                </div>
                            </div>
                        @else
                            <x-slot name="avatar">
                                {{ $item->initials ?? null }}
                            </x-slot>
                        @endif
                    @stack('item_information_end')
                </x-show.summary.left>

                <x-show.summary.right>
                    <div class="w-1/2 sm:w-1/3 text-center group">
                        <div class="relative text-xl sm:text-6xl text-purple group-hover:text-purple-700 mb-2">
                            {{ $item->inventory()->sum('opening_stock') }}
                            <span class="w-8 absolute left-0 right-0 m-auto -bottom-1 bg-gray-200 transition-all group-hover:bg-gray-900" style="height: 1px;"></span>
                        </div>

                        <span class="font-light mt-3">
                            {{ trans('inventory::general.stock') }}
                        </span>
                    </div>

                    <div class="w-1/2 sm:w-1/3 text-center group">
                        <x-tooltip id="tooltip-summary-income" placement="top" message="{!! $summary_amounts['income_exact'] !!}">
                            <div class="relative text-xl sm:text-6xl text-purple group-hover:text-purple-700 mb-2">
                                {!! $summary_amounts['income_for_humans'] !!}
                                <span class="w-8 absolute left-0 right-0 m-auto -bottom-1 bg-gray-200 transition-all group-hover:bg-gray-900" style="height: 1px;"></span>
                            </div>

                            <span class="font-light mt-3">
                                {{ trans_choice('general.incomes', 1) }}
                            </span>
                        </x-tooltip>
                    </div>
                    
                    <div class="w-1/2 sm:w-1/3 text-center group">
                        <x-tooltip id="tooltip-summary-expense" placement="top" message="{!! $summary_amounts['expense_exact'] !!}">
                            <div class="relative text-xl sm:text-6xl text-purple group-hover:text-purple-700 mb-2">
                                {!! $summary_amounts['expense_for_humans'] !!}
                                <span class="w-8 absolute left-0 right-0 m-auto -bottom-1 bg-gray-200 transition-all group-hover:bg-gray-900" style="height: 1px;"></span>
                            </div>

                            <span class="font-light mt-3">
                                {{ trans_choice('general.expenses', 1) }}
                            </span>
                        </x-tooltip>
                    </div>
                </x-show.summary.right>
            </x-show.summary>

            <x-show.content>
                <x-show.content.left>
                    @stack('item_sku_start')
                        @if ($item->sku)
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">{{ trans('inventory::general.sku') }}</div>
                                <span>{{ $item->inventory()->value('sku') }}</span>
                            </div>
                        @endif
                    @stack('item_sku_end')
        
                    @stack('item_sale_price_start')
                        @if ($item->sale_price)
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">{{ trans('items.sale_price') }}</div>
                                <span>
                                    <x-money :amount="$item->sale_price" :currency="setting('default.currency')" convert />
                                </span>
                            </div>
                        @endif
                    @stack('item_sale_price_end')

                    @stack('item_purchase_price_start')
                        @if ($item->purchase_price)
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">{{ trans('items.purchase_price') }}</div>
                                <span>
                                    <x-money :amount="$item->purchase_price" :currency="setting('default.currency')" convert />
                                </span>
                            </div>
                        @endif
                    @stack('item_purchase_price_end')
        
                    @stack('item_opening_stock_value_start')
                        @if ($item->inventory()->sum('opening_stock_value'))
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">{{ trans('inventory::items.opening_stock') }}</div>
                                <span>{{ $item->inventory()->sum('opening_stock_value') }}</span>
                            </div>
                        @endif
                    @stack('item_opening_stock_value_end')
        
                    @stack('item_unit_start')
                        @if ($item->inventory()->value('unit'))
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">{{ trans('inventory::general.unit') }}</div>
                                <span>{{ $item->inventory()->value('unit') }}</span>
                            </div>
                        @endif
                    @stack('item_unit_end')

                    @stack('item_returnable_start')
                        @if ($item->inventory()->value('returnable') == true)
                            <div class="flex flex-col text-sm mb-5">
                                <span class="badge badge-info">{{ trans('inventory::items.returnable') }}</span>
                            </div>
                        @endif
                    @stack('item_returable_end')
        
                    @stack('item_category_start')
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans_choice('general.categories', 1) }}</div>
                            <span>{{ $item->category->name }}</span>
                        </div>
                    @stack('item_category_end')
        
                    @stack('tax_input_start')
                        @if ($item->tax)
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">{{ trans_choice('general.taxes', 1) }}</div>
                                <span>{{ $item->tax->name }}</span>
                            </div>
                        @endif
                    @stack('tax_input_end')
        
                    @stack('item_barcode_start')
                        @if ($item->inventory()->value('barcode'))
                            <div class="flex flex-col text-sm mb-2">
                                <div class="font-medium">{{ trans('inventory::general.barcode') }}</div>
                            </div>
                            <div class="flex flex-col text-sm mb-5">
                                <div class="font-medium">
                                    <img src="{{ $barcode ? Storage::url($barcode->id) : asset('modules/Inventory/Resources/assets/img/barcode/code_128.png') }}" class="image-style">
                                </div>
                                <span>{{ $item->inventory()->value('barcode') ?? 'brcd123456789' }}</span>
                            </div>
                        @endif
                    @stack('item_barcode_end')
                </x-show.content.left>

                <x-show.content.right>
                        <x-tabs active="overview">
                            <x-slot name="navs">
                                @stack('overview_nav_start')
                                    <x-tabs.nav
                                        id="overview"
                                        name="{{ trans('inventory::general.overview') }}"
                                        active
                                        class="relative px-8 text-sm text-black text-center pb-2 cursor-pointer transition-all border-b swiper-slide"
                                    />
                                @stack('overview_nav_end')

                                @stack('histories_nav_start')
                                    <x-tabs.nav
                                        id="histories"
                                        name="{{ trans_choice('inventory::general.histories', 1) }}"
                                        class="relative px-8 text-sm text-black text-center pb-2 cursor-pointer transition-all border-b swiper-slide"
                                    />
                                @stack('histories_nav_end')
                            </x-slot>
    
                            <x-slot name="content">
                                @stack('overview_tab_start')
                                    <x-tabs.tab id="overview">
                                        @widget('Modules\Inventory\Widgets\Items\IncomeLineChart', $item)
                                        <div class="flex flex-col sm:flex-row">
                                            @widget('Modules\Inventory\Widgets\Items\TotalCurrentStock', $item)
                                            @widget('Modules\Inventory\Widgets\Items\WarehouseReorderLevel', $item)
                                        </div>
                                    </x-tabs.tab>
                                @stack('overview_tab_start')

                                @stack('histories_tab_start')
                                    <x-tabs.tab id="histories">
                                        <x-table>
                                            <x-table.thead>
                                                <x-table.tr>
                                                    <x-table.th class="w-5/12 sm:w-4/12">
                                                        <x-slot name="first">
                                                            <x-sortablelink
                                                                column="date"
                                                                title="{{ trans('general.date') }}"
                                                                :query="['filter' => 'active, visible']"
                                                                :arguments="['class' => 'col-aka', 'rel' => 'nofollow']"
                                                            />
                                                        </x-slot>
                                                        <x-slot name="second">
                                                            <x-sortablelink column="number" title="{{ trans_choice('general.numbers', 1) }}" />
                                                        </x-slot>
                                                    </x-table.th>

                                                    <x-table.th class="w-4/12 sm:w-3/12">
                                                        {{ trans('inventory::general.action') . ' ' . trans_choice('general.types', 1) }}
                                                    </x-table.th>
        
                                                    <x-table.th class="w-3/12" hidden-mobile>
                                                        {{ trans_choice('inventory::general.warehouses', 1) }}
                                                    </x-table.th>
        
                                                    <x-table.th class="w-3/12 sm:w-2/12" kind="right">
                                                        {{ trans('invoices.quantity') }}
                                                    </x-table.th>
                                                </x-table.tr>
                                            </x-table.thead>
        
                                            <x-table.tbody>
                                                @foreach($item_histories as $item)
                                                    <x-table.tr>
                                                        <x-table.td class="w-5/12 sm:w-4/12">
                                                            <x-slot name="first" class="font-bold truncate" override="class">
                                                                <x-date date="{{ $item->created_at }}" />
                                                            </x-slot>
                                                            <x-slot name="second">
                                                                <a href="{{ url($item->action_url) }}" class="border-black border-b border-dashed">{{ $item->number ? '' . $item->number . '' : '---' }}</a>
                                                            </x-slot>
                                                        </x-table.td>
        
                                                        <x-table.td class="w-4/12 sm:w-3/12">
                                                            {{ $item->action_type }}
                                                        </x-table.td>
        
                                                        <x-table.td class="w-3/12" hidden-mobile>
                                                            <a href="{{ route('inventory.warehouses.show', [$item->warehouse_id]) }}" class="border-black border-b border-dashed">{{ $item->warehouse->name }}</a>
                                                        </x-table.td>
        
                                                        <x-table.td class="w-3/12 sm:w-2/12" kind="right">
                                                            {{ $item->operation_type . $item->quantity }}
                                                        </x-table.td>
                                                    </x-table.tr>
                                                @endforeach
                                            </x-table.tbody>
                                        </x-table>
        
                                        <x-pagination :items="$item_histories" type="item_histories"/>
                                    </x-tabs.tab>
                                @stack('histories_tab_end')
                            </x-slot>
                        </x-tabs>
                </x-show.content.right>
            </x-show.content>
        </x-show.container>
    </x-slot>

    <x-script alias="inventory" file="items" />
</x-layouts.admin>
