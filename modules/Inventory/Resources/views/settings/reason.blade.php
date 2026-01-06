<div class="small-table-width">
    <x-table class="flex flex-col divide-y divide-gray-200">
        <x-table.thead>
            <x-table.tr>
                <x-table.th class="w-full ml-3">
                    {{ trans('inventory::transferorders.reason') }}
                </x-table.th>
            </x-table.tr>
        </x-table.thead>

        <x-table.tbody>
            <x-table.tr class="relative flex items-center px-1 group/actions border-b" v-for="(row, index) in form.reasons" ::index="index">
                <x-table.td class="w-10/12">
                    <x-form.group.text name="reasons[][reason_value]" value="" data-item="reason_value" v-model="row.reason_value" />
                </x-table.td>

                <x-table.td class="w-2/12 none-truncate" override="class">
                    <x-button type="button" @click="onDeleteReason(index)" class="px-3 py-1.5 mb-3 sm:mt-2 sm:mb-0 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                        <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                    </x-button>
                </x-table.td>
            </x-table.tr>
            <x-table.tr id="addItem">
                <x-table.td class="w-full">
                    <x-button type="button" override="class" @click="onAddReason" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                        <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                        {{ trans('general.form.add', ['field' => trans('inventory::transferorders.reason')]) }}
                    </x-button>
                </x-table.td>
            </x-table.tr>
        </x-table.tbody>
    </x-table>
</div>