<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.edit', ['type' => trans_choice('inventory::general.adjustments', 1)]) }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="adjustment" method="PATCH" :route="['inventory.adjustments.update', $adjustment->id]" :model="$adjustment">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="adjustment_number" label="{{ trans('inventory::adjustments.adjustment_number') }}" :value="$adjustment->adjustment_number"/>
                        
                        <x-form.group.date name="date" label="{{ trans('general.date') }}" icon="calendar_today" value="{{ Date::parse($adjustment->date)->toDateString() }}" show-date-format="{{ company_date_format() }}" date-format="Y-m-d" autocomplete="off" />

                        <akaunting-select
                            class="required sm:col-span-3"
                            :form-classes="[{'has-error': form.errors.get('warehouse_id') }]"
                            :title="'{{ trans_choice('inventory::general.warehouses',1) }}'"
                            :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('inventory::general.warehouses',1)]) }}'"
                            :name="'warehouse_id'"
                            :options="{{ $warehouses }}"
                            :value="'{{ $adjustment->warehouse_id }}'"
                            @interface="form.warehouse_id = $event"
                            @change="onChangeType()"
                            :form-error="form.errors.get('warehouse_id')"
                            :no-data-text="'{{ trans('general.no_data') }}'"
                            :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                        ></akaunting-select>

                        <x-form.group.select name="reason" label="{{ trans('inventory::transferorders.reason') }}" :options="$reasons" :selected="$adjustment->reason" form-group-class="sm:col-span-3" />

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" not-required/>
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
                                            <x-table.th class="w-3/12">
                                                {{ trans_choice('general.items', 1) }}
                                            </x-table.th>
                                
                                            <x-table.th class="w-3/12 text-center">
                                                {{ trans('inventory::transferorders.item_quantity') }}
                                            </x-table.th>
                                
                                            <x-table.th class="w-3/12 text-center">
                                                {{ trans('inventory::adjustments.adjusted_quantity') }}
                                            </x-table.th>

                                            <x-table.th class="w-3/12 text-center">
                                                {{ trans('inventory::adjustments.new_quantity') }}
                                            </x-table.th>
                                        </x-table.tr>
                                    </x-table.thead>
                                
                                    <x-table.tbody>
                                        <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(row, index) in form.items" ::index="index">                                   
                                            <x-table.td class="w-3/12">
                                                <akaunting-select
                                                    class="sm:col-span-12"
                                                    :class="[{'show': items}]"
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
                                                <x-form.group.text 
                                                    value=""
                                                    name="items[][item_quantity]"
                                                    disabled
                                                    data-item="item_quantity" 
                                                    v-model="row.item_quantity"
                                                    @change="form.errors.clear('items.' + index + '.item_quantity')" 
                                                    v-error="form.errors.has('items.' + index + '.item_quantity')"
                                                    v-error-message="form.errors.get('items.' + index + '.item_quantity')"
                                                />
                                            </x-table.td>
                                        
                                            <x-table.td class="w-3/12">
                                                <div class="flex flex-col">
                                                    <input value=""
                                                        class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                                                        data-item="adjusted_quantity"
                                                        required="required"
                                                        name="items.' + index + '.adjusted_quantity'"
                                                        v-model="row.adjusted_quantity"
                                                        @input="onChangeNewQuantity(index)"
                                                        type="text"
                                                        autocomplete="off">
                                        
                                                    <span class="invalid-feedback block text-xs text-red whitespace-normal" v-if="adjustment_button"
                                                        v-if="form.errors.has('items.' + index + '.adjusted_quantity')"
                                                        v-html="form.errors.get('items.' + index + '.adjusted_quantity')">
                                                    </span>
                                                </div>
                                            </x-table.td>
                                        
                                            <x-table.td class="w-2/12">
                                                <x-form.group.text
                                                    value="" 
                                                    name="items[][new_quantity]"
                                                    disabled
                                                    data-item="new_quantity" 
                                                    v-model="row.new_quantity"
                                                    @change="form.errors.clear('items.' + index + '.new_quantity')" 
                                                    v-error="form.errors.has('items.' + index + '.new_quantity')"
                                                    v-error-message="form.errors.get('items.' + index + '.new_quantity')"
                                                />
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
                        <div v-if="! adjustment_button">
                            <x-form.buttons cancel-route="inventory.adjustments.index" />
                        </div>
                        <div v-else>
                            <div class="flex items-center justify-end sm:col-span-6">
                                <x-link href="{{ route('inventory.adjustments.index') }}" class="px-6 py-1.5 hover:bg-gray-200 rounded-lg ltr:mr-2 rtl:ml-2" override="class">
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
            var adjustment_items = {!! json_encode($adjustment_items) !!};
        </script>
    @endpush

    <x-script alias="inventory" file="adjustments" />
</x-layouts.admin>
