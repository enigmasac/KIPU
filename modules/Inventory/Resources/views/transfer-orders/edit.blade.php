<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.edit', ['type' => trans_choice('inventory::general.transfer_orders', 1)]) }}</x-slot>
    
    <x-slot name="content">
        <x-form.container>
            <x-form id="transfer-order" method="PATCH" :route="['inventory.transfer-orders.update', $transfer_order->id]" :model="$transfer_order">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="transfer_order" label="{{ trans_choice('inventory::general.transfer_orders', 1) }}" />

                        <x-form.group.text name="transfer_order_number" label="{{ trans_choice('inventory::transferorders.transfer_order_number', 1) }}"/>
                        
                        <x-form.group.date name="date" label="{{ trans('general.date') }}" icon="calendar_today" value="{{ Date::parse($transfer_order->date)->toDateString() }}" show-date-format="{{ company_date_format() }}" date-format="Y-m-d" autocomplete="off" />

                        <x-form.group.text name="reason" label="{{ trans('inventory::transferorders.reason') }}" not-required />

                        <akaunting-select
                            class="required sm:col-span-3"
                            :form-classes="[{'has-error': form.errors.get('source_warehouse_id') }]"
                            :title="'{{ trans('inventory::transferorders.source_warehouse') }}'"
                            :placeholder="'{{ trans('general.form.select.field', ['field' => trans('inventory::transferorders.source_warehouse')]) }}'"
                            :name="'source_warehouse_id'"
                            :options="{{ $warehouses }}"
                            :value="'{{ $transfer_order->source_warehouse_id }}'"
                            @interface="form.source_warehouse_id = $event"
                            @change="onChangeType()"
                            :form-error="form.errors.get('source_warehouse_id')"
                            :no-data-text="'{{ trans('general.no_data') }}'"
                            :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                        ></akaunting-select>

                        <akaunting-select
                            class="required sm:col-span-3"
                            :class="[{'show': items}]"
                            :form-classes="[{'has-error': form.errors.get('destination_warehouse_id') }]"
                            :title="'{{ trans('inventory::transferorders.destination_warehouse') }}'"
                            :placeholder="'{{ trans('general.form.select.field', ['field' => trans('inventory::transferorders.destination_warehouse')]) }}'"
                            :name="'destination_warehouse_id'"
                            :options="{{ $warehouses }}"
                            :value="'{{ $transfer_order->destination_warehouse_id}}'"
                            @interface="form.destination_warehouse_id = $event"
                            :form-error="form.errors.get('destination_warehouse_id')"
                            :no-data-text="'{{ trans('general.no_data') }}'"
                            :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                        ></akaunting-select>
                    </x-slot>
                </x-form.section>

                <x-form.section v-if="items">
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('inventory::general.items', 2) }}" />
                    </x-slot>

                    <x-slot name="body">
                        <div class="sm:col-span-6 overflow-x-scroll large-overflow-unset">
                            <div class="small-table-width">
                                <x-table class="flex flex-col divide-y divide-gray-200">
                                    <x-table.thead>
                                        <x-table.tr>
                                            <x-table.th class="w-5/12">
                                                {{ trans_choice('general.items', 1) }}
                                            </x-table.th>
                                
                                            <x-table.th class="w-3/12 text-center">
                                                {{ trans('inventory::transferorders.item_quantity') }}
                                            </x-table.th>
                                
                                            <x-table.th class="w-4/12 text-center">
                                                {{ trans('inventory::transferorders.transfer_quantity') }}
                                            </x-table.th>
                                        </x-table.tr>
                                    </x-table.thead>
                                
                                    <x-table.tbody>
                                        <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(row, index) in form.items" ::index="index">
                                            <x-table.td class="w-5/12">
                                                <akaunting-select
                                                    class="sm:col-span-12"
                                                    v-if="items"
                                                    :form-classes="[{'has-error': form.errors.get('item') }]"
                                                    :placeholder="'{{ trans('general.form.select.field',
                                                    ['field' => trans_choice('general.items', 1)]) }}'"
                                                    name="items[][item_id]"
                                                    :dynamic-options="options.item_id"
                                                    :value="row.item_id"
                                                    @interface="row.item_id = $event"
                                                    @change="onChangeItemQuantity(index)"
                                                    :form-error="form.errors.get('item_id')"
                                                    :no-data-text="'{{ trans('general.no_data') }}'"
                                                    :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                                                    >
                                                </akaunting-select>
                                            </x-table.td>
                                
                                            <x-table.td class="w-3/12">
                                                <x-form.group.text name="items[][item_quantity]" data-item="item_quantity" v-model="row.item_quantity" />
                                            </x-table.td>
                                
                                            <x-table.td class="w-3/12">
                                                <input value=""
                                                    class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                                                    data-item="transfer_quantity"
                                                    required="required"
                                                    name="items.' + index + '.transfer_quantity'"
                                                    v-model="row.transfer_quantity"
                                                    type="text"
                                                    autocomplete="off"
                                                    @input="onChangeQuantity(index)"
                                                >
                                                        
                                                <span class="invalid-feedback block text-xs text-red whitespace-normal" v-if="transfer_button"
                                                    v-if="form.errors.has('items.' + index + '.transfer_quantity')"
                                                    v-html="form.errors.get('items.' + index + '.transfer_quantity')">
                                                </span>
                                            </x-table.td>

                                            <x-table.td class="w-1/12 none-truncate" override="class">
                                                <x-button type="button" @click="onDeleteItem(index)" class="px-3 py-1.5 mt-5 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                                                    <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                                                </x-button>
                                            </x-table.td>
                                        </x-table.tr>

                                        <x-table.tr id="addItem">
                                            <x-table.td class="w-full">
                                                <x-button type="button" override="class" @click="onAddItem" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                                                    <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                                                    <div class="group">
                                                        <span class="to-black-400 group-hover:bg-full-2 bg-no-repeat bg-0-2 bg-0-full bg-gradient-to-b from-transparent transition-backgroundSize cursor-pointer">
                                                        {{ trans('general.form.add', ['field' => trans_choice('general.items', 1)]) }}
                                                        </span>
                                                    </div>
                                                </x-button>
                                            </x-table.td>
                                        </x-table.tr>
                                    </x-table.tbody>
                                </x-table>
                            </div>
                        </div>
                    </x-slot>              
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <div v-if="! transfer_button">
                            <x-form.buttons cancel-route="inventory.transfer-orders.index" />
                        </div>
                        <div v-else>
                            <div class="flex items-center justify-end sm:col-span-6">
                                <x-link href="{{ route('inventory.transfer-orders.index') }}" class="px-6 py-1.5 hover:bg-gray-200 rounded-lg ltr:mr-2 rtl:ml-2" override="class">
                                    {{ trans('general.cancel') }}
                                </x-link>
                        
                                <x-button
                                    type="submit"
                                    class="relative flex items-center justify-center bg-green hover:bg-green-700 text-white px-6 py-1.5 text-base rounded-lg disabled:bg-green-100"
                                    disabled
                                    override="class"
                                >
                                    <span :class="[{'opacity-0': form.loading}]">
                                        {{ trans('general.save') }}
                                    </span>
                                </x-button>
                            </div>
                        </div>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>

    @push('scripts_start')
        <script type="text/javascript">
            var negative_stock = '{{ setting('inventory.negative_stock') }}'
            var variant_items = {!! json_encode($transfer_order_items) !!};
        </script>
    @endpush

    <x-script alias="inventory" file="transfer_orders" />
</x-layouts.admin>
