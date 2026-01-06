<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.edit', ['type' => trans_choice('inventory::general.item_groups', 1)]) }}</x-slot>

    <x-slot name="content">
        <x-index.container>
            <x-form id="item-group" method="PATCH" :route="['inventory.item-groups.update', $item_group->id]" :model="$item_group">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <div class="sm:col-span-3 grid gap-x-8 gap-y-6">
                            <x-form.group.text name="name" label="{{ trans('general.name') }}" />

                            <x-form.group.category type="item" not-required />
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.group.file name="picture" label="{{ trans_choice('general.pictures', 1) }}" not-required form-group-class="sm:col-span-3 sm:row-span-2" />
                        </div>

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" not-required/>

                        <x-form.group.select multiple add-new name="tax_ids" label="{{ trans_choice('general.taxes', 1) }}" :options="$taxes" :selected="$item_group->items[0]->tax_ids" not-required :path="route('modals.taxes.create')" :field="['key' => 'id', 'value' => 'title']" form-group-class="sm:col-span-3 el-select-tags-pl-38" />

                        <x-form.group.select name="unit" label="{{ trans('inventory::general.unit') }}" :options="$units" :selected="$item_group->items[0]->item->inventory()->value('unit') ?? ''" form-group-class="sm:col-span-3" />

                        <x-form.input.hidden name="type" value="item" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('inventory::general.variants', 2) }}" />
                    </x-slot>
                    <x-slot name="body">
                        <div class="sm:col-span-6 overflow-x-scroll large-overflow-unset">
                            <div class="small-table-width">
                                <x-table class="flex flex-col divide-y divide-gray-200">
                                    <x-table.thead>
                                        <x-table.tr>                          
                                            <x-table.th class="w-3/12">
                                                {{ trans('general.name') }}
                                                <label class="text-red-600">*</label>
                                            </x-table.th>
                                
                                            <x-table.th class="w-9/12 text-center">
                                                {{ trans_choice('inventory::variants.values',  2) }}
                                                <label class="text-red-600">*</label>
                                            </x-table.th>
                                        </x-table.tr>
                                    </x-table.thead>
                                
                                    <x-table.tbody>
                                        <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(variant_row, variant_index) in form.variants">                                   
                                            <x-table.td class="w-3/12">
                                                <akaunting-select
                                                    class="form-element-sm d-inline-block col-md-12"
                                                    :name="'items.' + variant_index + '.variant_id'"
                                                    :data-field="'variants'"
                                                    :options="{{ json_encode($variants) }}"
                                                    :value="variant_row.variant_id"
                                                    @interface="variant_row.variant_id = $event"
                                                    @change="getVariantsValue($event, variant_index)"
                                                    {{-- :form-error="form.errors.get('items.' + variant_index + '.variant_id')" --}}
                                                    :no-data-text="'{{ trans('general.no_data') }}'"
                                                    :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                                                ></akaunting-select>
                                            </x-table.td>
                                        
                                            <x-table.td class="w-8/12">
                                                <el-select
                                                    :disabled="!selected_variants[variant_index].variant_values.length"
                                                    v-model="form.variants[variant_index].variant_values"
                                                    @change="onAddVariantValue($event, variant_index)"
                                                    multiple
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
                                                <x-button type="button" @click="onDeleteVariant(index)" class="px-3 py-1.5 mt-5 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                                                    <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                                                </x-button>
                                            </x-table.td>
                                        </x-table.tr>
                                        
                                        <x-table.tr id="addItem" v-show="! add_variant_disabled">
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
                                            @include('inventory::item-groups.price')
                                        </x-tabs.tab>
                                
                                        <x-tabs.tab id="inventory">
                                            @include('inventory::item-groups.inventory')
                                        </x-tabs.tab>
                                    </x-slot>
                                </x-tabs>
                            </div>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.group.switch name="enabled" label="{{ trans('general.enabled') }}" />
                
                @can('update-inventory-item-groups')
                    <x-form.section>
                        <x-slot name="foot">
                            <x-form.buttons cancel-route="inventory.item-groups.index" />
                        </x-slot>
                    </x-form.section>
                @endcan
            </x-form>
        </x-index.container>
    </x-slot>

    @push('scripts_start')
        <script type="text/javascript">
            var select_variants = {!! json_encode($item_group->variants()->get()) !!};

            var items_group = {!! json_encode($items) !!};
        </script>
    @endpush
    
    <x-script alias="inventory" file="item_groups" />
</x-layouts.admin>
