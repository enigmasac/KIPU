<x-layouts.admin>
    <x-slot name="title">
        {{ $transfer_order->transfer_order }}
    </x-slot>

    <x-slot name="status">
        <x-show.status
            text-status="{{ trans('inventory::transferorders.' . $transfer_order->status) }}"
            background-color="bg-status-partial"
            text-color="text-text-partial"
        />
    </x-slot>
    
    <x-slot name="buttons">
        <x-link href="{{ route('inventory.transfer-orders.create') }}" kind="primary">
            {{ trans('general.title.new', ['type' => trans_choice('inventory::general.transfer_orders', 1)]) }}
        </x-link>
        
        <x-dropdown id="dropdown-actions">
            <x-slot name="trigger">
                <span class="material-icons">more_horiz</span>
            </x-slot>
         
            <x-dropdown.link href="{{ route('inventory.transfer-orders.edit', $transfer_order->id) }}">
                {{ trans('general.edit') }}
            </x-dropdown.link>
        
            <x-dropdown.link href="{{ route('inventory.transfer-orders.print', $transfer_order->id) }}" target="_blank">
                {{ trans('general.print') }}
            </x-dropdown.link>

            <x-dropdown.link href="{{ route('inventory.transfer-orders.download', $transfer_order->id) }}" class="">
                {{ trans('general.download_pdf') }}
            </x-dropdown.link>

            <x-delete-link :model="$transfer_order" route="inventory.transfer-orders.destroy" :text="trans_choice('inventory::general.transfer_orders', 1)" />
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        <div class="flex flex-col lg:flex-row my-10 lg:space-x-24 rtl:space-x-reverse space-y-12 lg:space-y-0">
            <div class="w-full lg:w-5/12 space-y-12">
                <x-show.accordion type="create" :open="($transfer_order->status == 'create')">
                    <x-slot name="head">
                        <x-show.accordion.head
                            title="{{ trans('general.create') }}"
                            description="{{ trans('inventory::transferorders.accordion.description', ['date' => Date::parse($transfer_order->created_at)->format('d M Y H:i')])}}"
                        />
                    </x-slot>
                
                    <x-slot name="body">
                        <div class="flex">
                            <x-link href="{{ route('inventory.transfer-orders.edit', $transfer_order->id) }}" >
                                {{ trans('general.edit') }}
                            </x-link>
                        </div>
                    </x-slot>
                </x-show.accordion>

                <x-show.accordion type="draft" :open="($transfer_order->status == 'draft')">
                    @php
                        $created_date = null;

                        if (isset($transfer_order->ready->created_at)) {
                            $created_date = Date::parse($transfer_order->ready->created_at)->format('d M Y H:i');
                        } else if(!isset($transfer_order->ready->created_at ) && ($transfer_order->status == 'ready' ||$transfer_order->status == 'in_transfer' || $transfer_order->status == 'transferred')) {
                            $created_date = Date::parse($transfer_order->histories[count($transfer_order->histories)-1]->created_at)->format('d M Y H:i');
                        }
                    @endphp

                    <x-slot name="head">
                        <x-show.accordion.head
                            title="{{ trans('inventory::transferorders.ready_to_transfer') }}"
                            description="{{ trans('inventory::transferorders.accordion.description', ['date' => $created_date])}}"
                        />
                    </x-slot>
                
                    <x-slot name="body">
                        <div class="flex">
                            @if ($transfer_order->status != 'draft')
                                <x-link href="{{ route('inventory.transfer-orders.ready', $transfer_order->id) }}" class="px-3 py-1.5 mb-3 sm:mb-0 rounded-xl text-sm font-medium leading-6 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 disabled" >
                                    {{ trans('inventory::transferorders.ready') }}
                                </x-link>
                            @else
                                <x-link href="{{ route('inventory.transfer-orders.ready', $transfer_order->id) }}">
                                    {{ trans('inventory::transferorders.ready') }}
                                </x-link>
                            @endif
                        </div>
                    </x-slot>
                </x-show.accordion>

                <x-show.accordion type="ready" :open="($transfer_order->status == 'ready')">
                    @php
                        $created_date = null;

                        if (isset($transfer_order->in_transfer->created_at)) {
                            $created_date = Date::parse($transfer_order->in_transfer->created_at)->format('d M Y H:i');
                        } else if (!isset($transfer_order->in_transfer->created_at ) && ($transfer_order->status == 'in_transfer' || $transfer_order->status == 'transferred')) {
                            $created_date = Date::parse($transfer_order->histories[count($transfer_order->histories)-1]->created_at)->format('d M Y H:i');
                        }
                    @endphp

                    <x-slot name="head">
                        <x-show.accordion.head
                            title="{{ trans('inventory::transferorders.in_transfer') }}"
                            description="{{ trans('inventory::transferorders.accordion.description', ['date' => $created_date])}}"
                        />
                    </x-slot>
                
                    <x-slot name="body">
                        <div class="flex">
                            @if ($transfer_order->status != 'in_transfer' || $transfer_order->status == 'transferred')
                                <x-link href="{{ route('inventory.transfer-orders.in-transfer', $transfer_order->id) }}" class="px-3 py-1.5 mb-3 sm:mb-0 rounded-xl text-sm font-medium leading-6 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 disabled" >
                                    {{ trans('inventory::transferorders.in_transfer') }}
                                </x-link>
                            @else
                                <x-link href="{{ route('inventory.transfer-orders.in-transfer', $transfer_order->id) }}">
                                    {{ trans('inventory::transferorders.in_transfer') }}
                                </x-link>
                            @endif
                        </div>
                    </x-slot>
                </x-show.accordion>

                <x-show.accordion type="in_transfer" :open="($transfer_order->status == 'in_transfer')">
                    @php
                        $created_date = null;

                        if (isset($transfer_order->transferred->created_at)) {
                            $created_date = Date::parse($transfer_order->transferred->created_at)->format('d M Y H:i');
                        }
                    @endphp

                    <x-slot name="head">
                        <x-show.accordion.head
                            title="{{ trans('inventory::transferorders.transferred') }}"
                            description="{{ trans('inventory::transferorders.accordion.description', ['date' => $created_date])}}"
                        />
                    </x-slot>
                
                    <x-slot name="body">
                        <div class="flex">
                            @if ($transfer_order->status == 'transferred')
                                <x-link href="{{ route('inventory.transfer-orders.transferred', $transfer_order->id) }}" class="px-3 py-1.5 mb-3 sm:mb-0 rounded-xl text-sm font-medium leading-6 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 disabled" >
                                    {{ trans('inventory::transferorders.transferred') }}
                                </x-link>
                            @else
                                <x-link href="{{ route('inventory.transfer-orders.transferred', $transfer_order->id) }}">
                                    {{ trans('inventory::transferorders.transferred') }}
                                </x-link>
                            @endif
                        </div>
                    </x-slot>
                </x-show.accordion>
            </div>
        
            <div class="w-full lg:w-7/12">
                <div class="p-7 shadow-2xl rounded-2xl">
                    {{-- company information--}}
                    <table class="border-bottom-1">
                        <tr>
                            <td style="width:20%; padding: 0 0 15px 0;" valign="top">
                                <img src="{{ company()->logo }}" height="70" width="70" alt="{{ setting('company.name') }}" />
                            </td>
                    
                            <td class="text" style="width: 80%; padding: 0 0 15px 0;">
                                <h2 class="text-semibold text">
                                    {{ setting('company.name') }}
                                </h2>
                
                                <p>{!! (setting('company.address')) !!}</p>
                
                                @if (setting('company.tax_number'))
                                    <p>
                                        {{ trans('general.tax_number') }}: {{ setting('company.tax_number') }}
                                    </p>
                                @endif
                
                                @if (setting('company.phone'))
                                    <p>
                                        {{ setting('company.phone') }}
                                    </p>
                                @endif
                
                                <p>{{ setting('company.email') }}</p>
                            </td>
                        </tr>
                    </table>

                    {{-- title information --}}
                    <table>
                        <tr>
                            <td style="width: 60%; padding: 15px 0 15px 0;">
                                <h2 style="font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::general.information') }}
                                </h2>
                            </td>
                        </tr>
                    </table>
                    
                    <table>
                        @stack('source_warehouse_name_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::transferorders.source_warehouse') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $transfer_order->source_warehouse->name }}
                                </td>
                            </tr>
                        @stack('source_warehouse_name_input_end')
                        
                        @stack('destination_warehouse_name_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::transferorders.destination_warehouse') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $transfer_order->destination_warehouse->name }}
                                </td>
                            </tr>
                        @stack('destination_warehouse_name_input_end')
                        
                        @stack('transfer_order_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::transferorders.name') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $transfer_order->transfer_order }}
                                </td>
                            </tr>
                        @stack('transfer_order_input_end')

                        @stack('transfer_order_number_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::transferorders.number') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $transfer_order->transfer_order_number }}
                                </td>
                            </tr>
                        @stack('transfer_order_number_input_end')
                
                        @stack('date_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::transferorders.date') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    <x-date date="{{ $transfer_order->date }}" />
                                </td>
                            </tr>
                        @stack('date_input_end')
                    </table>
                    
                    <div class="row">
                        <div class="col-100">
                            <div class="text extra-spacing">
                                <table class="lines">
                                    <thead style="background-color:#55588b !important; -webkit-print-color-adjust: exact;">
                                        <tr>
                                            <th class="item text text-semibold text-left text-white" style="border-radius: 10px 0px 0px 10px;">
                                                {{ trans_choice('general.items', 2)}}
                                            </th>
        
                                            <th class="quantity text text-semibold text-white" style="border-radius: 0px 10px 10px 0px;">
                                                {{ trans('inventory::general.quantity')}}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($transfer_order->transfer_order_items->count())
                                            @foreach($transfer_order->transfer_order_items as $item)
                                                <tr>
                                                    <td class="item text">{{ $item->item->name}} <br/> </td>
                                        
                                                    <td class="quantity text">{{ $item->transfer_quantity }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text text-center empty-items">
                                                    {{ trans('documents.empty_items') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                
                </div>
            </div>
        </div>
    </x-slot>

    @push('scripts_start')
        <link rel="stylesheet" href="{{ asset('public/css/print.css?v=' . version('short')) }}" type="text/css">
    @endpush

    <x-script alias="inventory" file="transfer_orders" />
</x-layouts.admin>
