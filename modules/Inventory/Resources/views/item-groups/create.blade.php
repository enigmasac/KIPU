<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.new', ['type' => trans_choice('inventory::general.item_groups', 1)]) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans('general.title.new', ['type' => trans_choice('inventory::general.item_groups', 1)]) }}"
        icon="ballot"
        route="inventory.item-groups.create"
    ></x-slot>

    <x-slot name="content">
        <x-index.container>
            <x-form id="item-group" route="inventory.item-groups.store">
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
                    
                        <x-form.group.select multiple add-new name="tax_ids" label="{{ trans_choice('general.taxes', 1) }}" :options="$taxes" :selected="(setting('default.tax')) ? [setting('default.tax')] : null" not-required :path="route('modals.taxes.create')" :field="['key' => 'id', 'value' => 'title']" form-group-class="sm:col-span-3 el-select-tags-pl-38" />

                        <x-form.group.select name="unit" label="{{ trans('inventory::general.unit') }}" :options="$units" :selected="setting('inventory.default_unit')" form-group-class="sm:col-span-3" />
                    
                        <x-form.input.hidden name="type" value="item" />

                        <x-form.input.hidden name="enabled" value=1 />
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
                                        @include('inventory::item-groups.variant')
                                        
                                        <x-table.tr id="addItem" v-show="! add_variant_disabled">
                                            <x-table.td class="w-full">
                                                <x-button type="button" override="class" @click="onAddVariant" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                                                    <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                                                    {{ trans('general.form.add', ['field' => trans_choice('inventory::general.variants', 1)]) }}
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

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons cancel-route="inventory.item-groups.index" />
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-index.container>
    </x-slot>

    <x-script alias="inventory" file="item_groups" />
</x-layouts.admin>
