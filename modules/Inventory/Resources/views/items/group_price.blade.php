<div class="small-table-width">
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

                <x-table.th class="w-2/12 text-center">
                    Afecto a Detracción (%)
                </x-table.th>
            </x-table.tr>
        </x-table.thead>

        <x-table.tbody>
            <x-table.tr class="relative flex items-start px-1 group/actions border-b"
                v-for="(group_row, group_index) in form.group_items">
                <x-table.td class="w-3/12">
                    <div class="flex flex-col"
                        :class="[{'has-error': form.errors.has('group_items.' + group_index + '.name') }]">
                        <input
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="name" name="group_items.' + group_index + '.name'" v-model="group_row.name"
                            type="text" autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.name')">
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.name')"
                            v-html="form.errors.get('items.' + group_index + '.name')">
                        </span>
                    </div>

                    <x-form.input.hidden data-item="variant_value_id" name="group_items[][variant_value_id]"
                        v-model="group_row.variant_value_id" />
                    <x-form.input.hidden name="enabled" value=1 />
                </x-table.td>

                <x-table.td class="w-3/12">
                    <div class="flex flex-col"
                        :class="[{'has-error': form.errors.has('group_items.' + group_index + '.sku') }]">
                        <input
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="sku" name="group_items.' + group_index + '.sku'" v-model="group_row.sku"
                            type="text" autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.sku')">
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.sku')"
                            v-html="form.errors.get('items.' + group_index + '.sku')">
                        </span>
                    </div>
                </x-table.td>

                <x-table.td class="w-2/12">
                    <div class="flex flex-col"
                        :class="[{'has-error': form.errors.has('group_items.' + group_index + '.barcode') }]">
                        <input
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="barcode" name="group_items.' + group_index + '.barcode'"
                            v-model="group_row.barcode" type="text" autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.barcode')">
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.barcode')"
                            v-html="form.errors.get('items.' + group_index + '.barcode')">
                        </span>
                    </div>
                </x-table.td>

                <x-table.td class="w-2/12">
                    <div class="flex flex-col"
                        :class="[{'has-error': form.errors.has('group_items.' + group_index + '.sale_price') }]">
                        <input
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="sale_price" name="group_items.' + group_index + '.sale_price'"
                            v-model="group_row.sale_price" type="text" autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.sale_price')">
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.sale_price')"
                            v-html="form.errors.get('items.' + group_index + '.sale_price')">
                        </span>
                    </div>
                </x-table.td>

                <x-table.td class="w-2/12">
                    <div class="flex flex-col"
                        :class="[{'has-error': form.errors.has('group_items.' + group_index + '.purchase_price') }]">
                        <input
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="purchase_price" name="group_items.' + group_index + '.purchase_price'"
                            v-model="group_row.purchase_price" type="text" autocomplete="off"
                            @change="form.errors.clear('group_items.' + group_index + '.purchase_price')">
                        <span class="invalid-feedback block text-xs text-red whitespace-normal"
                            v-if="form.errors.has('group_items.' + group_index + '.purchase_price')"
                            v-html="form.errors.get('items.' + group_index + '.purchase_price')">
                        </span>
                    </div>
                </x-table.td>

                <x-table.td class="w-2/12">
                     <div class="flex flex-col">
                        <input
                            class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                            data-item="detraction_percentage"
                            name="group_items.' + group_index + '.detraction_percentage'"
                            v-model="group_row.detraction_percentage"
                            type="text"
                            autocomplete="off"
                            placeholder="0"
                        >
                        <!-- Auto-enable is_detraction if percentage > 0 -->
                        <input type="hidden" name="group_items.' + group_index + '.is_detraction'" :value="group_row.detraction_percentage > 0 ? 1 : 0">
                    </div>
                </x-table.td>
            </x-table.tr>
        </x-table.tbody>
    </x-table>
</div>