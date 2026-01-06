<x-layouts.admin>
    <x-slot name="title">
        {{ $adjustment->adjustment_number }}
    </x-slot>

    <x-slot name="buttons">
        <x-link href="{{ route('inventory.adjustments.create') }}" kind="primary">
            {{ trans('general.title.new', ['type' => trans_choice('inventory::general.adjustments', 1)]) }}
        </x-link>
        
        <x-dropdown id="dropdown-actions">
            <x-slot name="trigger">
                <span class="material-icons">more_horiz</span>
            </x-slot>
         
            <x-dropdown.link href="{{ route('inventory.adjustments.edit', $adjustment->id) }}">
                {{ trans('general.edit') }}
            </x-dropdown.link>
        
            <x-dropdown.link href="{{ route('inventory.adjustments.print', $adjustment->id) }}" target="_blank">
                {{ trans('general.print') }}
            </x-dropdown.link>

            <x-dropdown.link href="{{ route('inventory.adjustments.download', $adjustment->id) }}" class="">
                {{ trans('general.download_pdf') }}
            </x-dropdown.link>

            <x-delete-link :model="$adjustment" route="inventory.adjustments.destroy" :text="trans_choice('inventory::general.adjustments', 1)" />
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        <div class="flex flex-col lg:flex-row my-10 lg:space-x-24 rtl:space-x-reverse space-y-12 lg:space-y-0">
            <div class="w-full lg:w-12/12">
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
                        @stack('adjustment_number_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::adjustments.adjustment_number') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $adjustment->adjustment_number }}
                                </td>
                            </tr>
                        @stack('adjustment_number_input_end')

                        @stack('warehouse_name_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans_choice('inventory::general.warehouses', 1) }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $adjustment->warehouse->name }}
                                </td>
                            </tr>
                        @stack('warehouse_name_input_end')
                
                        @stack('reason_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('inventory::transferorders.reason') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $adjustment->reason }}
                                </td>
                            </tr>
                        @stack('reason_input_end')
                        
                        @stack('description_input_start')
                            <tr>
                                <td valign="top" style="width: 30%; margin: 0px; padding: 8px 4px 0 0; font-size: 12px; font-weight:600;">
                                    {{ trans('general.description') }}:
                                </td>
                    
                                <td valign="top" class="border-bottom-dashed-black" style="width:70%; margin: 0px; padding:  8px 0 0 0; font-size: 12px;">
                                    {{ $adjustment->description }}
                                </td>
                            </tr>
                        @stack('description_input_end')
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
                                        @if ($adjustment->adjustment_items->count())
                                                @foreach($adjustment->adjustment_items as $item)
                                                <tr>
                                                    <td class="item text">{{ $item->item->name}} <br/> </td>
                                        
                                                    <td class="quantity text">{{ $item->adjusted_quantity }}</td>
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

    <x-script alias="inventory" file="adjustments" />
</x-layouts.admin>

