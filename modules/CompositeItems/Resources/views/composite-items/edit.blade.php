<x-layouts.admin>
    @php
        $sunat_units_options = [
            'NIU' => 'NIU (UNIDAD)',
            'ZZ' => 'ZZ (SERVICIO)',
            'KGM' => 'KGM (KILOGRAMOS)',
            'LTR' => 'LTR (LITROS)',
            'BX' => 'BX (CAJA)',
            'GLL' => 'GLL (GALONES)',
            'MTR' => 'MTR (METROS)',
            'TNE' => 'TNE (TONELADAS)',
        ];
    @endphp
    <x-slot
        name="title">{{ trans('general.title.edit', ['type' => trans_choice('composite-items::general.composite_items', 1)]) }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="composite-item" method="PATCH" :route="['composite-items.composite-items.update', $composite_item->id]" :model="$composite_item">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}"
                            description="{{ trans('items.form_description.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.radio name="type" label="{{ trans_choice('general.types', 1) }}" :options="[
        'product' => trans_choice('general.products', 1),
        'service' => trans_choice('general.services', 1)
    ]" checked="{{ $composite_item->item->type }}"
                            @input="onType($event)" />

                        <div class="sm:col-span-3 grid gap-x-8 gap-y-6">
                            <x-form.group.text name="name" label="{{ trans('general.name') }}"
                                :value="$composite_item->item->name" />

                            <x-form.group.text name="sku" label="{{ trans('invoices.sku') }}"
                                :value="$composite_item->item->sku" required />

                            <x-form.group.select name="sunat_unit_code" label="{{ trans('invoices.unit') }}"
                                :options="$sunat_units_options" :selected="$composite_item->item->sunat_unit_code"
                                required />

                            <x-form.group.category type="item" :selected="$composite_item->item->category_id" />
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.group.file name="picture" label="{{ trans_choice('general.pictures', 1) }}"
                                not-required form-group-class="sm:col-span-3 sm:row-span-2" />
                        </div>

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}"
                            :value="$composite_item->item->description" not-required />

                    </x-slot>
                </x-form.section>

                <x-form.group.switch name="enabled" :value="$composite_item->item->enabled"
                    label="{{ trans('general.enabled') }}" />

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('items.billing') }}"
                            description="{{ trans('items.form_description.billing') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.checkbox name="sale_information" id="item-sale-information"
                            :checked="$composite_item->item->sale_information" :options="['sale' => trans('items.sale_information')]" @input="onInformation($event, 'sale')"
                            form-group-class="sm:col-span-3" checkbox-class="sm:col-span-6" />

                        <x-form.group.checkbox name="purchase_information" id="item-purchase-information"
                            :checked="$composite_item->item->purchase_information" :options="['sale' => trans('items.purchase_information')]" @input="onInformation($event, 'purchase')"
                            form-group-class="sm:col-span-3" checkbox-class="sm:col-span-6" />

                        <x-form.group.text name="sale_price" label="{{ trans('items.sale_price') }}"
                            :value="$composite_item->item->sale_price" v-bind:disabled="sale_information" />

                        <x-form.group.text name="purchase_price" label="{{ trans('items.purchase_price') }}"
                            :value="$composite_item->item->purchase_price" v-bind:disabled="purchase_information" />

                        <x-form.group.select multiple add-new name="tax_ids"
                            label="{{ trans_choice('general.taxes', 1) }}" :options="$taxes"
                            :selected="$composite_item->item->tax_ids" not-required :path="route('modals.taxes.create')"
                            :field="['key' => 'id', 'value' => 'title']"
                            form-group-class="sm:col-span-3 el-select-tags-pl-38" />

                        <x-form.group.toggle name="is_detraction" label="Afecto a Detracción"
                            v-model="form.is_detraction" :checked="$composite_item->item->is_detraction" />

                        <div v-if="form.is_detraction == 1" class="sm:col-span-3 grid grid-cols-2 gap-x-8 gap-y-6">
                            <x-form.group.select name="detraction_code" label="Código de Detracción"
                                v-model="form.detraction_code" :value="$composite_item->item->detraction_code"
                                :selected="$composite_item->item->detraction_code" :options="[
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
    ]" @change="window.onDetractionCodeChange($event, form)" />

                            <x-form.group.text name="detraction_percentage" label="Porcentaje de Detracción (%)"
                                v-model="form.detraction_percentage"
                                :value="$composite_item->item->detraction_percentage" />
                        </div>
                    </x-slot>
                </x-form.section>

                @if ($inventory == true)
                    <x-form.section>
                        <x-slot name="head">
                            <x-form.section.head title="{{ trans('inventory::general.name') }}"
                                description="{{ trans('inventory::general.description') }}" />
                        </x-slot>

                        <x-slot name="body">
                            <x-form.group.checkbox name="returnable" id="item-returable"
                                :checked="$composite_item->returnable" :options="['1' => trans('inventory::general.returnable')]" @input="onCanReturnable($event)"
                                form-group-class="sm:col-span-2" checkbox-class="sm:col-span-6" />

                            <x-form.group.checkbox name="track_inventory" id="item-track-inventory"
                                :checked="$composite_item->track_inventory" :options="['1' => trans('inventory::items.track_inventory')]" @input="onCanTrack($event)"
                                form-group-class="sm:col-span-2" checkbox-class="sm:col-span-6" />
                        </x-slot>
                    </x-form.section>
                @endif

                <x-form.section v-if="form.track_inventory == true">
                    <x-slot name="body">
                        <x-form.group.text name="barcode" label="{{ trans('inventory::general.barcode')}}"
                            form-group-class="sm:col-span-3" not-required />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="body">
                        <div class="sm:col-span-6">
                            <div v-if="form.track_inventory == true">
                                @include('composite-items::partials.inventory-item')
                            </div>

                            <div v-else>
                                @include('composite-items::partials.item')
                            </div>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons cancel-route="composite-items.composite-items.index" />
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>

    @push('scripts_start')
        <script type="text/javascript">
            var composite_items = {!! json_encode($composite_items) !!};

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

    <x-script alias="composite-items" file="composite-items" />
</x-layouts.admin>