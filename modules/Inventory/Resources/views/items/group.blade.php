
<label class="control-label">{{ trans_choice('inventory::general.variants', 2) }}</label>
<label class="text-red-600">*</label>

<div class="small-table-width">
    <x-table class="flex flex-col divide-y divide-gray-200">
        <x-table.thead>
            <x-table.tr>
                <x-table.th class="w-3/12">
                    {{ trans('general.name') }}
                    <label class="text-red-600">*</label>
                </x-table.th>

                <x-table.th class="w-8/12 text-center">
                    {{ trans('inventory::variants.values') }}
                    <label class="text-red-600">*</label>
                </x-table.th>

                <x-table.th class="w-1/12"></x-table.th>
            </x-table.tr>
        </x-table.thead>

        <x-table.tbody>
            <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(variant_row, variant_index) in form.variants">
                <x-table.td class="w-3/12">
                    <akaunting-select
                        class="form-element-sm d-inline-block sm:col-span-12"
                        :name="'group_items.' + variant_index + '.variant_id'"
                        :icon="'fas fa-align-justify'"
                        :data-field="'variants'"
                        :options="{{ json_encode($variants) }}"
                        :value="'{{ old('variant_id') }}'"
                        @interface="variant_row.variant_id = $event"
                        @change="getVariantsValue($event, variant_index)"
                        :form-error="form.errors.get('group_items.' + variant_index + '.variant_id')"
                        :no-data-text="'{{ trans('general.no_data') }}'"
                        :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                    ></akaunting-select>
                </x-table.td>

                <x-table.td class="w-8/12">
                    <el-select
                        class="form-element-sm d-inline-block sm:col-span-12 h-100"
                        :disabled="!selected_variants[variant_index].variant_values.length"
                        v-model="form.variants[variant_index].variant_values"
                        @change="onAddVariantValue($event, variant_index)"
                        multiple
                        filterable
                        select-all
                        @remove-tag="onDeleteVariantValue"
                        placeholder="Select Value"
                    >
                        <el-option
                            v-for="variant in selected_variants[variant_index].variant_values"
                            :disabled="form.variants[variant_index].variant_values.includes(variant.value)"
                            :key="variant.value"
                            :label="variant.label"
                            :value="variant.value">
                        </el-option>
                    </el-select>

                    <input name="fake" data-field="variants" type="hidden" v-model="variant_row.fake">
                </x-table.td>

                <x-table.td class="w-1/12 none-truncate">
                    <x-button type="button" @click="onDeleteVariant(variant_index)" class="px-3 py-1.5 mt-5 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                        <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                    </x-button>
                </x-table.td>
            </x-table.tr>
            
            <x-table.tr id="addItem" v-show="!add_variant_disabled">
                <x-table.td class="w-full">
                    <x-button type="button" override="class" @click="onAddVariant" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                        <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                        <div class="group">
                            <span class="to-black-400 group-hover:bg-full-2 bg-no-repeat bg-0-2 bg-0-full bg-gradient-to-b from-transparent transition-backgroundSize cursor-pointer">
                                {{ trans('general.form.add', ['field' => trans_choice('inventory::general.variants', 1)]) }}
                            </span>
                        </div>
                    </x-button>
                </x-table.td>                            
            </x-table.tr>
        </x-table.tbody>
    </x-table>
</div>

<x-tabs class="flex items-center mt-10 mb-5" override="class" active="price">
    <x-slot name="navs">
        <x-tabs.nav
            id="price"
            name="{{ trans('inventory::items.general_information') }}"
            active
            class="relative px-8 text-sm text-black text-center pb-2 cursor-pointer transition-all border-b swiper-slide"
        />
        <x-tabs.nav
            id="inventory"
            name="{{ trans('inventory::general.name') }}"
            class="relative px-8 text-sm text-black text-center pb-2 cursor-pointer transition-all border-b swiper-slide"
        />
    </x-slot>

    <x-slot name="content">
        <x-tabs.tab id="price">
            @include('inventory::items.group_price')
        </x-tabs.tab>

        <x-tabs.tab id="inventory">
            @include('inventory::items.group_inventory')
        </x-tabs.tab>
    </x-slot>
</x-tabs>
