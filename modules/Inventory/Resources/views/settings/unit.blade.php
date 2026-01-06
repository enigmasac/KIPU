<div class="small-table-width">
    <x-table class="flex flex-col divide-y divide-gray-200">
        <x-table.thead>
            <x-table.tr >
                <x-table.th class="w-3/12 ml-3">
                    {{ trans('inventory::general.default') }}
                </x-table.th>
                <x-table.th class="w-9/12 ml-3">
                    {{ trans('inventory::general.unit') }}
                </x-table.th>
            </x-table.tr>
        </x-table.thead>

        <x-table.tbody>
            <x-table.tr class="relative flex items-center px-1 group/actions border-b" v-for="(row, index) in form.items" ::index="index">
                <x-table.td class="w-2/12 ml-7">
                    <input type="checkbox"
                        name="items[][default_unit]"
                        :id="'default-unit-' + index"
                        data-item="default_unit"
                        @change="onChangeDefault(index)"
                        v-model="row.default_unit"
                        class="rounded-sm text-purple border-gray-300 cursor-pointer disabled:bg-gray-200 focus:outline-none focus:ring-transparent" />
                    <label style="margin-left: %50" :for="'default-unit-' + index" class="custom-control-label unit-checkbox"></label>
                </x-table.td>

                <x-table.td class="w-8/12">
                    <x-form.group.text name="items[][unit_value]" value="" data-item="unit_value" v-model="row.unit_value" />
                </x-table.td>

                <x-table.td class="w-2/12 none-truncate" override="class">
                    <x-button type="button" @click="onDeleteUnit(index)" class="px-3 py-1.5 mb-3 sm:mt-2 sm:mb-0 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                        <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                    </x-button>
                </x-table.td>
            </x-table.tr>
            <x-table.tr id="addItem">
                <x-table.td class="w-full">
                    <x-button type="button" override="class" @click="onAddUnit" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                        <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                        {{ trans('general.form.add', ['field' => trans('inventory::general.unit')]) }}
                    </x-button>
                </x-table.td>
            </x-table.tr>
        </x-table.tbody>
    </x-table>
</div>
