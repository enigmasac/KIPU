<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.new', ['type' => trans_choice('inventory::general.adjustments', 1)]) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans('general.title.new', ['type' => trans_choice('inventory::general.adjustments', 1)]) }}"
        icon="rule"
        route="inventory.adjustments.create"
    ></x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="adjustment" route="inventory.adjustments.store">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="adjustment_number" label="{{ trans('inventory::adjustments.adjustment_number') }}" :value="$adjustment_number"/>
                        
                        <x-form.group.date name="date" label="{{ trans('general.date') }}" icon="calendar_today" value="{{ request()->get('date', Date::now()->toDateString()) }}" show-date-format="{{ company_date_format() }}" date-format="Y-m-d" autocomplete="off" />

                        <akaunting-select
                            class="required sm:col-span-3"
                            :form-classes="[{'has-error': form.errors.get('warehouse_id') }]"
                            :title="'{{ trans_choice('inventory::general.warehouses',1) }}'"
                            :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('inventory::general.warehouses',1)]) }}'"
                            :name="'warehouse_id'"
                            :options="{{ $warehouses }}"
                            :value="'{{ old('warehouse_id') }}'"
                            @interface="form.warehouse_id = $event"
                            @change="onChangeType()"
                            :form-error="form.errors.get('warehouse_id')"
                            :no-data-text="'{{ trans('general.no_data') }}'"
                            :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                        ></akaunting-select>

                        <x-form.group.select name="reason" label="{{ trans('inventory::transferorders.reason') }}" :options="$reasons" form-group-class="sm:col-span-3" />

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
                                        @include('inventory::adjustments.items')

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
        </script>
    @endpush

    <x-script alias="inventory" file="adjustments" />
</x-layouts.admin>

