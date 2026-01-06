<x-layouts.admin>
    @php
        $sunat_units_options = [
            'NIU' => 'NIU (UNIDAD)',
            'ZZ'  => 'ZZ (SERVICIO)',
            'KGM' => 'KGM (KILOGRAMOS)',
            'LTR' => 'LTR (LITROS)',
            'BX'  => 'BX (CAJA)',
            'GLL' => 'GLL (GALONES)',
            'MTR' => 'MTR (METROS)',
            'TNE' => 'TNE (TONELADAS)',
        ];
    @endphp
    <x-slot name="title">{{ trans('general.title.edit', ['type' => trans_choice('general.items', 1)]) }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="item" method="PATCH" :route="['inventory.items.update', $item->id]" :model="$item">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" description="{{ trans('items.form_description.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.radio
                            name="type"
                            label="{{ trans_choice('general.types', 1) }}"
                            :options="[
                                'product' => trans_choice('general.products', 1),
                                'service' => trans_choice('general.services', 1)
                            ]"
                            checked="{{ $item->type }}"
                            @input="onType($event)"
                        />

                        <div class="sm:col-span-3 grid gap-x-8 gap-y-6">
                            <x-form.group.text name="name" label="{{ trans('general.name') }}" />

                            <x-form.group.text name="sku" label="{{ trans('invoices.sku') }}" :value="$item->sku" required />

                            <x-form.group.select name="sunat_unit_code" label="{{ trans('invoices.unit') }}" :options="$sunat_units_options" :selected="$item->sunat_unit_code" required />

                            <x-form.group.category type="item" not-required/>
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.group.file name="picture" label="{{ trans_choice('general.pictures', 1) }}" not-required form-group-class="sm:col-span-3 sm:row-span-2" />
                        </div>

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" not-required />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('items.billing') }}" description="{{ trans('items.form_description.billing') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.checkbox name="sale_information" id="item-sale-information" :options="['sale' => trans('items.sale_information')]" @input="onInformation($event, 'sale')" form-group-class="sm:col-span-3" checkbox-class="sm:col-span-6" />

                        <x-form.group.checkbox name="purchase_information" id="item-purchase-information" :options="['sale' => trans('items.purchase_information')]" @input="onInformation($event, 'purchase')" form-group-class="sm:col-span-3" checkbox-class="sm:col-span-6" />

                        <x-form.group.text name="sale_price" label="{{ trans('items.sale_price') }}" v-bind:disabled="sale_information" />

                        <x-form.group.text name="purchase_price" label="{{ trans('items.purchase_price') }}" v-bind:disabled="purchase_information" />

                        <x-form.group.select multiple add-new name="tax_ids" label="{{ trans_choice('general.taxes', 1) }}" :options="$taxes" :selected="$item->tax_ids" not-required :path="route('modals.taxes.create')" :field="['key' => 'id', 'value' => 'title']" form-group-class="sm:col-span-3 el-select-tags-pl-38" />

                        <x-form.group.toggle name="is_detraction" label="Afecto a Detracción"
                            v-model="form.is_detraction" :checked="$item->is_detraction" />

                        <div v-if="form.is_detraction == 1" class="sm:col-span-3 grid grid-cols-2 gap-x-8 gap-y-6">
                            <x-form.group.select name="detraction_code" label="Código de Detracción"
                                v-model="form.detraction_code" :value="$item->detraction_code"
                                :selected="$item->detraction_code" :options="[
        '001' => '001 - Azúcar y melaza de caña',
        '002' => '002 - Arroz',
        '003' => '003 - Alcohol etílico',
        '004' => '004 - Recursos hidrobiológicos',
        '005' => '005 - Maíz amarillo duro',
        '009' => '009 - Arena y piedra',
        '010' => '010 - Residuos, subproductos, desechos, recortes y desperdicios',
        '012' => '012 - Transporte de carga',
        '017' => '017 - Harina de trigo',
        '022' => '022 - Otros servicios empresariales',
        '037' => '037 - Demás servicios gravados con el IGV',
    ]"      @change="window.onDetractionCodeChange($event, form)" />

                            <x-form.group.text name="detraction_percentage" label="Porcentaje de Detracción (%)"
                                v-model="form.detraction_percentage" :value="$item->detraction_percentage" />
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.group.switch name="enabled" label="{{ trans('general.enabled') }}" />

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('inventory::general.name') }}" description="{{ trans('inventory::general.description') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.checkbox name="returnable" id="item-returable" :options="['returnable' => trans('inventory::general.returnable')]" @input="onCanReturnable($event)" form-group-class="sm:col-span-2" checkbox-class="sm:col-span-6" :checked="$item->inventory()->value('returnable')"/>

                        <x-form.group.checkbox name="track_inventory" id="item-track-inventory" :options="['true' => trans('inventory::items.track_inventory')]" @input="onCanTrack($event)" form-group-class="sm:col-span-2" checkbox-class="sm:col-span-6" :checked="$item->inventory()->first()"/>
                    </x-slot>             
                </x-form.section>

                <x-form.section v-if="form.track_inventory.length || form.track_inventory == true">
                    <x-slot name="body">
                        <x-form.group.text name="sku" label="{{ trans('inventory::general.sku')}}" :value="$sku" form-group-class="sm:col-span-3" />

                        <x-form.group.select name="unit" label="{{ trans('inventory::general.unit') }}" :options="$units" :selected="$item->inventory()->value('unit') ?? old('default_unit', setting('inventory.default_unit'))" form-group-class="sm:col-span-3" />

                        <x-form.group.text 
                            name="barcode" 
                            label="{{ trans('inventory::general.barcode')}}" 
                            :value="$item->inventory()->value('barcode')" 
                            form-group-class="sm:col-span-3" 
                            not-required
                            @input="onChangeBarcode()"
                        />

                        <div class="mt-9 sm:col-span-3 truncate">
                            <div class="items-center" v-html="barcode"></div>
                            <label v-html="barcode_label"></label>
                        </div>
                    
                        <div class="sm:col-span-6 overflow-x-scroll large-overflow-unset">
                            <x-table class="flex flex-col divide-y divide-gray-200">
                                <x-table.thead>
                                    <x-table.tr>                           
                                        <x-table.th class="w-5/12">
                                            {{ trans_choice('inventory::general.warehouses', 1) }}
                                            <label class="text-red-600">*</label>
                                        </x-table.th>
                            
                                        <x-table.th class="w-2/12 text-center">
                                            {{ trans('inventory::items.total_stock') }}
                                        </x-table.th>

                                        <x-table.th class="w-2/12 text-center">
                                            {{ trans('inventory::items.opening_stock') }}
                                            <label class="text-red-600">*</label>
                                        </x-table.th>
                            
                                        <x-table.th class="w-2/12 text-center">
                                            {{ trans('inventory::items.reorder_level') }}
                                        </x-table.th>

                                        <x-table.th class="w-1/12"></x-table.th>
                                    </x-table.tr>
                                </x-table.thead>
                            
                                <x-table.tbody>
                                    <x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(row, index) in form.items" ::index="index">                           
                                        <x-table.td class="w-5/12">
                                            <div class="flex flex-row">     
                                                <div class="form-element-sm d-inline-block w-9/12" :class="[{'has-error': form.errors.has('items.' + index + '.warehouse_id') }]">
                                                    <akaunting-select
                                                        class="form-element-sm d-inline-block w-9/12"
                                                        :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('inventory::general.warehouses', 1)])  }}'"
                                                        :name="'items.' + index + '.warehouse_id'"
                                                        :options="{{ json_encode($warehouses) }}"
                                                        :disabled-options="disabled_warehouse_options"
                                                        @change="onChangeWarehouse(index, $event)"
                                                        @clear="onClearWarehouseValue()"
                                                        :model="row.warehouse_id"
                                                        :value="row.warehouse_id"
                                                        @interface="row.warehouse_id = $event">
                                                    </akaunting-select>
                                                    <span class="invalid-feedback block text-xs text-red whitespace-normal"
                                                        v-if="form.errors.has('items.' + index + '.warehouse_id')"
                                                        v-html="form.errors.get('items.' + index + '.warehouse_id')">
                                                    </span>
                                                </div>

                                                <div class="w-3/12 p-3">
                                                    <label>
                                                        <input type="radio"
                                                            :name="'items.' + index + '.default_warehouse'"
                                                            :id="'default-warehouse-' + index"
                                                            data-item="default_warehouse"
                                                            :value="1"
                                                            @change="onChangeDefault(index)"
                                                            v-model="row.default_warehouse"
                                                        >
                                                            {{ trans('inventory::general.default') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </x-table.td>
                            
                                        <x-table.td class="w-2/12">
                                            <div class="flex flex-col" :class="[{'has-error': form.errors.has('items.' + index + '.opening_stock') }]">
                                                <input 
                                                    class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                                                    data-item="opening_stock"
                                                    name="items.' + index + '.opening_stock'"
                                                    v-model="row.opening_stock"
                                                    type="text"
                                                    autocomplete="off"
                                                    disabled
                                                >

                                                @if (! setting('inventory.negatif_stock'))
                                                    <span class="invalid-feedback block text-xs text-red whitespace-normal"
                                                        v-if="opening_stock_error[index] && form.errors.has('items.' + index + '.opening_stock')"
                                                        v-html="form.errors.get('items.' + index + '.opening_stock')">
                                                    </span>
                                                @endif

                                                <input type="hidden" data-item="default_opening_stock" name="items.' + index + '.default_opening_stock'" v-model="row.default_opening_stock">
                                            </div>
                                        </x-table.td>

                                        <x-table.td class="w-2/12">
                                                <input 
                                                    class="w-full text-sm px-3 py-2.5 mt-1 rounded-lg border border-light-gray text-black placeholder-light-gray bg-white disabled:bg-gray-200 focus:outline-none focus:ring-transparent focus:border-purple"
                                                    data-item="opening_stock_value"
                                                    required="required"
                                                    name="items.' + index + '.opening_stock_value'"
                                                    v-model="row.opening_stock_value"
                                                    type="text"
                                                    @input="onChangeOpeningStockValue(index, {{ setting('inventory.negatif_stock') }})"
                                                    autocomplete="off"
                                                >
    
                                                <input type="hidden" data-item="default_opening_stock_value" name="items[][default_opening_stock_value]" v-model="row.default_opening_stock_value">
                                        </x-table.td>

                                        <x-table.td class="w-2/12">
                                            <x-form.group.text name="items[][reorder_level]" data-item="reorder_level" v-model="row.reorder_level" />
                                        </x-table.td>

                                        <x-table.td class="w-1/12 none-truncate">
                                            <x-button type="button" @click="onDeleteItem(index)" class="px-3 py-1.5 mt-5 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
                                                <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
                                            </x-button>
                                        </x-table.td>
                                    </x-table.tr>

                                    <x-table.tr id="addItem" v-show="! add_item_disabled">
                                        <x-table.td class="w-full">
                                            <x-button type="button" override="class" @click="onAddItem" class="w-full text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                                                <span class="material-icons-outlined text-base font-bold mr-1">add</span>
                                                <div class="group">
                                                        <span class="to-black-400 group-hover:bg-full-2 bg-no-repeat bg-0-2 bg-0-full bg-gradient-to-b from-transparent transition-backgroundSize cursor-pointer">
                                                        {{ trans('general.form.add', ['field' => trans_choice('inventory::general.warehouses', 1)]) }}
                                                        </span>
                                                    </div>
                                            </x-button>
                                        </x-table.td>                            
                                    </x-table.tr>
                                </x-table.tbody>
                            </x-table>
                        </div>
                    </x-slot>              
                </x-form.section>

                @can('update-inventory-items')
                    <x-form.section>
                        <x-slot name="foot">
                            <x-form.buttons cancel-route="inventory.items.index" />
                        </x-slot>
                    </x-form.section>
                @endcan
            </x-form>
        </x-form.container>
    </x-slot>

    @push('scripts_start')
        <script type="text/javascript">
            var barcode_type = '{{ setting('inventory.barcode_type') }}'
            var inventory_items = {!! json_encode($inventory_items) !!};
            var warehouse_count = {{ $warehouses->count() }};
            var default_warehouse = '{{ setting("inventory.default_warehouse") }}';
            var text_can_not_negative ="{{ trans("inventory::items.can_not_be_negative") }}";

            window.detraction_rates = {
                '001': 10,
                '002': 4,
                '003': 10,
                '004': 4,
                '005': 4,
                '009': 10,
                '010': 15,
                '012': 4,
                '017': 4,
                '022': 12,
                '037': 12,
            };

            window.onDetractionCodeChange = function (code, form) {
                if (window.detraction_rates[code]) {
                    form.detraction_percentage = window.detraction_rates[code];
                }
            };
        </script>
    @endpush

    <x-script alias="inventory" file="items" />
</x-layouts.admin>
