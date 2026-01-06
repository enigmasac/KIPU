<div class="small-table-width">
    <x-table class="flex flex-col divide-y divide-gray-200">
        <x-table.thead>
            <x-table.tr>
                <x-table.th class="w-3/12">
                    {{ trans('general.name') }}
                    <label class="text-red-600">*</label>
                </x-table.th>

                <x-table.th class="w-3/12">
                    {{ trans_choice('inventory::general.warehouses', 1) }}
                    <label class="text-red-600">*</label>
                </x-table.th>

                <x-table.th class="w-3/12 text-center">
                    {{ trans('inventory::general.stock') }}
                    <label class="text-red-600">*</label>
                </x-table.th>

                <x-table.th class="w-3/12 text-center">
                    {{ trans('inventory::items.reorder_level') }}
                </x-table.th>
            </x-table.tr>
        </x-table.thead>

        <x-table.tbody>
            <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(group_row, group_index) in form.group_items">
                <x-table.td class="w-3/12">
                    <div class="flex flex-col" :class="[{'has-error': form.errors.has('group_items.' + group_index + '.name') }]">
                        <input  
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="name"
                            name="group_items.' + group_index + '.name'"
                            v-model="group_row.name"
                            type="text"
                            autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.name')" 
                        >
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.name')"
                            v-html="form.errors.get('items.' + group_index + '.name')">
                        </span>
                    </div>

                    <x-form.input.hidden data-item="variant_value_id" name="group_items[][variant_value_id]" v-model="group_row.variant_value_id" />
                </x-table.td>

                <x-table.td class="w-3/12">
                    <div>
                        <akaunting-select
                            class="form-element-sm d-inline-block sm:col-span-12"
                            :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('inventory::general.warehouses', 1)])  }}'"
                            :name="'group_items.' + group_index + '.warehouse_id'"
                            :icon="'fas fa-warehouse'"
                            :options="{{ json_encode($warehouses) }}"
                            :value="'{{ setting('inventory.default_warehouse') }}'"
                            @interface="group_row.warehouse_id = $event"
                        >
                        </akaunting-select>
                    </div>
                </x-table.td>

                <x-table.td class="w-3/12">
                    <div class="flex flex-col" :class="[{'has-error': form.errors.has('group_items.' + group_index + '.opening_stock') }]">
                        <input  
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="opening_stock"
                            name="group_items.' + group_index + '.opening_stock'"
                            v-model="group_row.opening_stock"
                            type="text"
                            autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.opening_stock')" 
                        >
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.opening_stock')"
                            v-html="form.errors.get('items.' + group_index + '.opening_stock')">
                        </span>
                    </div>
                </x-table.td>

                <x-table.td class="w-3/12">
                    <div class="flex flex-col" :class="[{'has-error': form.errors.has('group_items.' + group_index + '.reorder_level') }]">
                        <input  
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="reorder_level"
                            name="group_items.' + group_index + '.reorder_level'"
                            v-model="group_row.reorder_level"
                            type="text"
                            autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.reorder_level')" 
                        >
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.reorder_level')"
                            v-html="form.errors.get('items.' + group_index + '.reorder_level')">
                        </span>
                    </div>
                </x-table.td>
            </x-table.tr>
        </x-table.tbody>
    </x-table>
</div>