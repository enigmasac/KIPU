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
        <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(row, index) in form.items" ::index="index">
            <x-table.td class="w-3/12">
                <x-form.group.text 
                    ::name="'items.' + index + '.name'" 
                    value="" 
                    data-item="name" 
                    v-model="row.name" 
                    @change="form.errors.clear('items.' + index + '.name')" 
                    v-error="form.errors.has('items.' + index + '.name')"
                    v-error-message="form.errors.get('items.' + index + '.name')"
                />

                <x-form.input.hidden data-item="variant_value_id" name="items[][variant_value_id]" v-model="row.variant_value_id" />
            </x-table.td>

            <x-table.td class="w-3/12">
                <akaunting-select
                    class="form-element-sm d-inline-block col-md-12"
                    :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('inventory::general.warehouses', 1)])  }}'"
                    :name="'items.' + index + '.warehouse_id'"
                    :options="{{ json_encode($warehouses) }}"
                    :value="row.warehouse_id"
                    @interface="row.warehouse_id = $event"
                >
                </akaunting-select>

                <div class="invalid-feedback d-block text-red mt-1 whitespace-normal"
                    v-if="form.errors.has('items.' + index + '.warehouse_id')"
                    v-html="form.errors.get('items.' + index + '.warehouse_id')">
                </div>
            </x-table.td>

            <x-table.td class="w-3/12">
                <x-form.group.text 
                    ::name="'items.' + index + '.opening_stock'" 
                    value="" 
                    data-item="opening_stock" 
                    v-model="row.opening_stock" 
                    @change="form.errors.clear('items.' + index + '.opening_stock')" 
                    v-error="form.errors.has('items.' + index + '.opening_stock')"
                    v-error-message="form.errors.get('items.' + index + '.opening_stock')"
                />
            </x-table.td>

            <x-table.td class="w-3/12">
                <x-form.group.text 
                    ::name="'items.' + index + '.reorder_level'" 
                    value="" 
                    data-item="reorder_level" 
                    v-model="row.reorder_level" 
                    @change="form.errors.clear('items.' + index + '.reorder_level')" 
                    v-error="form.errors.has('items.' + index + '.reorder_level')"
                    v-error-message="form.errors.get('items.' + index + '.reorder_level')"
                />
            </x-table.td>
        </x-table.tr>
    </x-table.tbody>
</x-table>
