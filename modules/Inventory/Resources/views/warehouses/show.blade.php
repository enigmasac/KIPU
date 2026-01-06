<x-layouts.admin>
    <x-slot name="title">
        {{ $warehouse->name }}
    </x-slot>

    <x-slot name="status">
        @if (! $warehouse->enabled)
            <x-index.disable text="{{ trans_choice('inventory::general.warehouses', 1) }}" />
        @endif

        @if (setting('inventory.default_warehouse') == $warehouse->id)
            <x-index.default text="{{ trans('inventory::general.default_warehouse') }}" />
        @endif
    </x-slot>

    <x-slot name="favorite"
        title="{{ $warehouse->name }}"
        icon="warehouses"
        :route="['inventory.warehouses.show', $warehouse->id]"
    ></x-slot>

    <x-slot name="buttons">
        @can('update-inventory-warehouses')
            <x-link href="{{ route('inventory.warehouses.edit', $warehouse->id) }}">
                {{ trans('general.edit') }}
            </x-link>

            <x-dropdown id="warehouse-show">
                <x-slot name="trigger">
                    <span class="material-icons">more_horiz</span>
                </x-slot>

                @php
                    $export_show_route = empty(request()->input()) ? route('inventory.warehouses.export-show', $warehouse->id) : route('inventory.warehouses.export-show', $warehouse->id, request()->input());
                @endphp
                        
                <x-dropdown.link href="{{ $export_show_route }}">
                    {{ trans('general.export') }}
                </x-dropdown.link>
                
                @can('delete-inventory-warehouses')
                    <x-delete-link :model="$warehouse" route="inventory.warehouses.destroy" :text="trans_choice('inventory::general.warehouses', 1)" />
                @endcan
            </x-dropdown>
        @endcan
    </x-slot>

    <x-slot name="content">
        <x-show.container>
            <x-show.summary>
                <x-show.summary.left>
                    <x-slot name="avatar">
                        {{ $warehouse->initials }}
                    </x-slot>
                </x-show.summary.left>

                <x-show.summary.right>
                    <div class="w-1/2 sm:w-1/3 text-center group">
                        <div class="relative text-xl sm:text-6xl text-purple group-hover:text-purple-700 mb-2">
                            {{ $total_stock }}
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
                    @if ($warehouse->email)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans('general.email') }}</div>
                            <span>{{ $warehouse->email }}</span>
                        </div>
                    @endif

                    @if ($warehouse->phone)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans('general.phone') }}</div>
                            <span>{{ $warehouse->phone }}</span>
                        </div>
                    @endif

                    @if ($warehouse->city)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans_choice('general.cities', 1) }}</div>
                            <span>{{ $warehouse->city }}</span>
                        </div>
                    @endif
        
                    @if ($warehouse->zip_code)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans('general.zip_code') }}</div>
                            <span>{{ $warehouse->zip_code }}</span>
                        </div>
                    @endif
        
                    @if ($warehouse->state)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans('general.state') }}</div>
                            <span>{{ $warehouse->state }}</span>
                        </div>
                    @endif
        
                    @if ($warehouse->country)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans_choice('general.countries', 1) }}</div>
                            <span>{{ trans('countries.' . $warehouse->country) }}</span>
                        </div>
                    @endif
        
                    <div class="flex flex-col text-sm mb-5">
                        <div class="font-medium">{{ trans('inventory::warehouses.total_item') }}</div>
                        <span>{{ $warehouse->items->count(); }}</span>
                    </div>
        
                    @if ($warehouse->description)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans('general.description') }}</div>
                            <span>{{ $warehouse->description }}</span>
                        </div>
                    @endif

                    @if ($warehouse->address)
                        <div class="flex flex-col text-sm mb-5">
                            <div class="font-medium">{{ trans('general.address') }}</div>
                            <span>{{ $warehouse->address }}</span>
                        </div>
                    @endif
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

                                        @widget('Modules\Inventory\Widgets\Warehouses\TotalStockLineChart', $warehouse)
                                    </x-tabs.tab>
                                @stack('overview_tab_start')

                                @stack('histories_tab_start')
                                    <x-tabs.tab id="histories">
                                        <x-form.section>
                                            <x-slot name="head">
                                                <div class="border-b-2 flex items-center border-gray-200 pb-2 mt-4">
                                                    <h2 class="lg:text-lg font-medium text-black w-11/12">
                                                        {{ trans('inventory::general.stock') }}
                                                    </h2>

                                                    @php
                                                        $export_stock_route = empty(request()->input()) ? route('inventory.warehouses.export-stock', $warehouse->id) : route('inventory.warehouses.export-stock', $warehouse->id, request()->input());
                                                    @endphp

                                                    <x-link href="{{ $export_stock_route }}" class="px-3 py-1.5 rounded-xl text-sm font-medium leading-6 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50">
                                                        {{ trans('general.export') }}
                                                    </x-link>
                                                </div>
                                            </x-slot>
                        
                                            <x-slot name="body">
                                                <div class="sm:col-span-6">
                                                    <x-table>
                                                        <x-table.thead>
                                                            <x-table.tr>
                                                                <x-table.th class="w-10/12 sm:w-6/12">
                                                                    <x-slot name="first">
                                                                        {{ trans('general.name') }}
                                                                    </x-slot>
                                                                    <x-slot name="second">
                                                                        {{ trans_choice('general.categories', 1) }}
                                                                    </x-slot>
                                                                </x-table.th>
                    
                                                                <x-table.th class="w-2/12">
                                                                    {{ trans('inventory::general.stock') }}
                                                                </x-table.th>
                
                                                                <x-table.th class="w-4/12" kind="amount" hidden-mobile>
                                                                    <x-slot name="first">
                                                                        {{ trans('items.sale_price') }}
                                                                    </x-slot>
                                                                    <x-slot name="second">
                                                                        {{ trans('items.purchase_price') }}
                                                                    </x-slot>
                                                                </x-table.th>
                                                            </x-table.tr>
                                                        </x-table.thead>
                    
                                                        <x-table.tbody>
                                                            @if ($warehouse->core_items)
                                                                @foreach($warehouse->core_items as $item)
                                                                    @php
                                                                        $item = $item->with('inventory')->first();
                                                                    @endphp
                                                                    <x-table.tr>
                                                                        <x-table.td class="w-10/12 sm:w-6/12">
                                                                            <x-slot name="first" class="flex font-bold" override="class">
                                                                                <x-link href="{{ route('inventory.items.show', $item->id) }}" class="border-black border-b border-dashed" override="class">
                                                                                    {{ $item->name }}
                                                                                </x-link>
                                                                            </x-slot>
                                                                            <x-slot name="second" class="font-normal" override="class">
                                                                                {{ $item->category ? $item->category->name : trans('general.na') }}
                                                                            </x-slot>
                                                                        </x-table.td>
                        
                                                                        <x-table.td class="w-2/12">
                                                                            {{ $item->inventory()->where('warehouse_id', $warehouse->id)->value('opening_stock') }}
                                                                        </x-table.td>

                                                                        <x-table.td class="w-4/12" kind="amount" hidden-mobile>
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
                                                                    </x-table.tr>
                                                                @endforeach
                                                            @endif
                                                        </x-table.tbody>
                                                    </x-table>

                                                    <x-pagination :items="$warehouse->item_pagination" type="warehouse_items"/>
                                                </div>                    
                                            </x-slot>             
                                        </x-form.section>

                                        <x-form.section>
                                            <x-slot name="head">
                                                <div class="border-b-2 flex items-center border-gray-200 pb-2">
                                                    <h2 class="lg:text-lg font-medium text-black w-11/12">
                                                        {{ trans('inventory::general.menu.histories') }}
                                                    </h2>

                                                    @php
                                                        $export_history_route = empty(request()->input()) ? route('inventory.warehouses.export-history', $warehouse->id) : route('inventory.warehouses.export-history', $warehouse->id, request()->input());
                                                    @endphp

                                                    <a href="{{ $export_history_route }}" class="px-3 py-1.5 rounded-xl text-sm font-medium leading-6 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 aling-right">
                                                        {{ trans('general.export') }}
                                                    </a>
                                                </div>
                                            </x-slot>
                        
                                            <x-slot name="body">
                                                <div class="sm:col-span-6">
                                                    <x-table>
                                                        <x-table.thead>
                                                            <x-table.tr>
                                                                <x-table.th class="w-3/12">
                                                                    {{ trans('general.date') }}
                                                                </x-table.th>
            
                                                                <x-table.th class="w-4/12" hidden-mobile>
                                                                    {{ trans_choice('inventory::general.warehouses', 1) }}
                                                                </x-table.th>
                    
                                                                <x-table.th class="w-4/12 sm:w-3/12">
                                                                    {{ trans('inventory::general.action') . ' ' . trans_choice('general.types', 1) }}
                                                                </x-table.th>
                    
                                                                <x-table.th class="w-4/12 sm:w-2/12" kind="amount">
                                                                    {{ trans('inventory::general.stock') }}
                                                                </x-table.th>
                                                            </x-table.tr>
                                                        </x-table.thead>
                    
                                                        <x-table.tbody>
                                                            @foreach($warehouse->histories as $history)
                                                                @if ($history->item->id)
                                                                    <x-table.tr>
                                                                        <x-table.td class="w-3/12">
                                                                            <x-date date="{{ $history->created_at }}" />
                                                                        </x-table.td>
                        
                                                                        <x-table.td class="w-4/12" hidden-mobile>
                                                                            <x-slot name="first">
                                                                                <x-link href="{{ route('inventory.items.show', $history->item_id) }}" class="border-black border-b border-dashed" override="class">
                                                                                    {{ $history->item->name }}
                                                                                </x-link>
                                                                            </x-slot>
                                                                        </x-table.td>
                        
                                                                        <x-table.td class="w-4/12 sm:w-3/12">
                                                                            <x-slot name="first">
                                                                                <x-link href="{{  url($history->action_url) }}" class="border-black border-b border-dashed" override="class">
                                                                                    {{ $history->action_type }}
                                                                                </x-link>
                                                                            </x-slot>
                                                                        </x-table.td>
                        
                                                                        <x-table.td class="w-4/12 sm:w-2/12" kind="amount" >
                                                                            {{ $history->quantity }}
                                                                        </x-table.td>
                                                                    </x-table.tr>
                                                                @endif
                                                            @endforeach
                                                        </x-table.tbody>
                                                    </x-table>
                    
                                                    <x-pagination :items="$warehouse->history_pagination" type="histories"/>
                                                </div>
                                            </x-slot>             
                                        </x-form.section>
                                    </x-tabs.tab>
                                @stack('histories_tab_end')
                            </x-slot>
                        </x-tabs>
                </x-show.content.right>
            </x-show.content>
        </x-show.container>
    </x-slot>

    <x-script alias="inventory" file="warehouses" />
</x-layouts.admin>
