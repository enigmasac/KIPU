<x-table>
    <x-table.thead>
        <x-table.tr class="flex items-center px-1">
            <x-table.th class="w-7/12 hidden sm:table-cell">
                {{ trans_choice('general.items', 1) }}
            </x-table.th>

            <x-table.th class="w-5/12 hidden text-center sm:table-cell">
                {{ trans('composite-items::general.quantity') }}
            </x-table.th>
        </x-table.tr>
    </x-table.thead>

    <x-table.tbody>
        <x-table.tr data-table-list class="relative flex items-center border-b hover:bg-gray-100 px-1 group" v-for="(row, index) in form.items" ::index="index">
            <x-table.td class="w-7/12 hidden sm:table-cell">
                <akaunting-select
                    class="w-full"
                    :form-classes="[{'has-error': form.errors.get('item_id') }]"
                    :placeholder="'{{ trans('general.form.select.field',
                    ['field' => trans_choice('general.items', 1)]) }}'"
                    name="items[][item_id]"
                    :options="{{ $items }}"
                    :model="row.item_id"
                    @interface="row.item_id = $event"
                    :form-error="form.errors.get('item_id')"
                    :no-data-text="'{{ trans('general.no_data') }}'"
                    :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                ></akaunting-select>
            </x-table.td>

            <x-table.td class="w-4/12 hidden sm:table-cell">
                <input value=""
                    class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                    data-item="quantity"
                    required="required"
                    name="items[][quantity]"
                    v-model="row.quantity"
                    type="text"
                    autocomplete="off"
                >
            </x-table.td>

            <x-table.td class="w-1/12 hidden sm:table-cell none-truncate" override="class">
                <x-button type="button" @click="onDeleteItem(index)" class="px-3 py-1.5 mb-3 sm:mt-2 sm:mb-0 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                    <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                </x-button>
            </x-table.td>
        </x-table.tr>

        <x-table.tr id="addItem">
            <x-table.td class="w-full hidden sm:table-cell">
                <x-button type="button" override="class" @click="onAddItem" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                    <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                    {{ trans('general.form.add', ['field' => trans_choice('general.items', 1)]) }}
                </x-button>
            </x-table.td>
        </x-table.tr>
    </x-table.tbody>
</x-table>
