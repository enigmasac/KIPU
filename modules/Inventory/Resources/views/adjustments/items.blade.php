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
            :value="'{{ old('item_id') }}'"
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
