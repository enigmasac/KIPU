<x-table class="flex flex-col divide-y divide-gray-200">
    <x-table.thead>
        <x-table.tr>
            <x-table.th class="w-3/12">
                {{ trans('general.name') }}
                <label class="text-red-600">*</label>
            </x-table.th>

            <x-table.th class="w-3/12">
                {{ trans('inventory::general.sku') }}
            </x-table.th>

            <x-table.th class="w-2/12 text-center">
                {{ trans('inventory::general.barcode') }}
            </x-table.th>

            <x-table.th class="w-2/12 text-center">
                <x-tooltip placement="top" message="{{ trans('items.sale_price') }}">
                    {{ trans('inventory::general.sort_sale_price') }}
                    <label class="text-red-600">*</label>
                </x-tooltip>
            </x-table.th>

            <x-table.th class="w-2/12 text-center">
                <x-tooltip placement="top" message="{{ trans('items.purchase_price') }}">
                    {{ trans('inventory::general.sort_purchase_price') }}
                    <label class="text-red-600">*</label>
                </x-tooltip>
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
                <x-form.group.text 
                    ::name="'items.' + index + '.sku'" 
                    value="" 
                    data-item="sku" 
                    v-model="row.sku" 
                    @change="form.errors.clear('items.' + index + '.sku')" 
                    v-error="form.errors.has('items.' + index + '.sku')"
                    v-error-message="form.errors.get('items.' + index + '.sku')"
                />
            </x-table.td>

            <x-table.td class="w-2/12">
                <x-form.group.text 
                    ::name="'items.' + index + '.barcode'" 
                    value="" 
                    data-item="barcode" 
                    v-model="row.barcode" 
                    @change="form.errors.clear('items.' + index + '.barcode')" 
                    v-error="form.errors.has('items.' + index + '.barcode')"
                    v-error-message="form.errors.get('items.' + index + '.barcode')"
                />
            </x-table.td>

            <x-table.td class="w-2/12">
                <x-form.group.text 
                    ::name="'items.' + index + '.sale_price'" 
                    value="" 
                    data-item="sale_price" 
                    v-model="row.sale_price" 
                    @change="form.errors.clear('items.' + index + '.sale_price')" 
                    v-error="form.errors.has('items.' + index + '.sale_price')"
                    v-error-message="form.errors.get('items.' + index + '.sale_price')"
                />
            </x-table.td>

            <x-table.td class="w-2/12">
                <x-form.group.text 
                    ::name="'items.' + index + '.purchase_price'" 
                    value="" 
                    data-item="purchase_price" 
                    v-model="row.purchase_price" 
                    @change="form.errors.clear('items.' + index + '.purchase_price')" 
                    v-error="form.errors.has('items.' + index + '.purchase_price')"
                    v-error-message="form.errors.get('items.' + index + '.purchase_price')"
                />
            </x-table.td>
        </x-table.tr>
    </x-table.tbody>
</x-table>