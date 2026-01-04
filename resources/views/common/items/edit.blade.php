<x-layouts.admin>
    @php
        $sunat_units = config('peru-core.sunat.units', [['id' => 'NIU', 'name' => 'NIU (UNIDAD)']]);
        $sunat_units_options = collect($sunat_units)->pluck('name', 'id');

        $sunat_tax_types = config('peru-core.sunat.tax_types', [['id' => '10', 'name' => 'Gravado - Operación Onerosa']]);
        $sunat_tax_types_options = collect($sunat_tax_types)->pluck('name', 'id');
    @endphp

    <x-slot name="title">{{ trans('general.title.edit', ['type' => trans_choice('general.items', 1)]) }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="item" method="PATCH" :route="['items.update', $item->id]" :model="$item">
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
                        />

                        <x-form.group.text name="name" label="{{ trans('general.name') }}" />

                        <x-form.group.text name="sku" label="{{ trans('invoices.sku') }}" :value="$item->sku" required />

                        <x-form.group.select name="sunat_unit_code" label="{{ trans('invoices.unit') }}" :options="$sunat_units_options" :selected="$item->sunat_unit_code" required />

                        <x-form.group.category type="item" not-required/>

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" not-required />

                        <!-- SECCIÓN DETRACCIÓN (SUNAT) -->
                        <div class="sm:col-span-6 bg-gray-50 p-4 rounded-lg border border-gray-200 mt-4">
                            <div class="flex items-center space-x-4">
                                <x-form.group.toggle name="is_detraction" label="Afecto a Detracción" v-model="form.is_detraction" />
                                
                                <div v-if="form.is_detraction" class="flex-1 max-w-xs">
                                    <x-form.group.text 
                                        name="detraction_percentage" 
                                        label="Porcentaje de Detracción (%)" 
                                        v-model="form.detraction_percentage"
                                        placeholder="Ej. 10.00"
                                        required
                                    />
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Si está marcado, se aplicará automáticamente la detracción en facturas >= S/ 700.00 con IGV.
                            </p>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('items.billing') }}" description="{{ trans('items.form_description.billing') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.checkbox name="sale_information" id="item-sale-information" :options="['sale' => trans('items.sale_information')]" @input="onInformation($event, 'sale')" form-group-class="sm:col-span-3" checkbox-class="sm:col-span-6" />
                        <x-form.group.checkbox name="purchase_information" id="item-purchase-information" :options="['sale' => trans('items.purchase_information')]" @input="onInformation($event, 'purchase')" form-group-class="sm:col-span-3" checkbox-class="sm:col-span-6" />

                        <!-- 1. IMPUESTOS -->
                        <div class="sm:col-span-6 mb-4">
                            <x-form.group.tax name="tax_ids" id="tax_ids" multiple not-required />
                        </div>

                        <!-- SECCIÓN VENTA -->
                        <div class="sm:col-span-3 grid gap-y-4" v-show="!sale_information">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <x-form.label for="sale_price" required>Precio de Venta (Neto)</x-form.label>
                                    <label class="flex items-center text-xs text-gray-500 cursor-pointer">
                                        <input type="radio" v-model="form.sunat_sale_mode" value="net"> <span class="ml-1">Editar</span>
                                    </label>
                                </div>
                                <x-form.input.text 
                                    name="sale_price" 
                                    id="sale_price" 
                                    v-model="form.sale_price" 
                                    v-bind:disabled="form.sunat_sale_mode !== 'net'"
                                />
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <x-form.label for="sale_price_total" required>Precio Venta (Incl. Impuestos)</x-form.label>
                                    <label class="flex items-center text-xs text-gray-500 cursor-pointer">
                                        <input type="radio" v-model="form.sunat_sale_mode" value="total"> <span class="ml-1">Editar</span>
                                    </label>
                                </div>
                                <x-form.input.text 
                                    name="sale_price_total" 
                                    id="sale_price_total" 
                                    v-model="form.sale_price_total" 
                                    v-bind:disabled="form.sunat_sale_mode !== 'total'"
                                />
                            </div>
                        </div>

                        <!-- SECCIÓN COMPRA -->
                        <div class="sm:col-span-3 grid gap-y-4" v-show="!purchase_information">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <x-form.label for="purchase_price" required>Precio de Compra (Neto)</x-form.label>
                                    <label class="flex items-center text-xs text-gray-500 cursor-pointer">
                                        <input type="radio" v-model="form.sunat_purchase_mode" value="net"> <span class="ml-1">Editar</span>
                                    </label>
                                </div>
                                <x-form.input.text 
                                    name="purchase_price" 
                                    id="purchase_price" 
                                    v-model="form.purchase_price" 
                                    v-bind:disabled="form.sunat_purchase_mode !== 'net'"
                                />
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <x-form.label for="purchase_price_total" required>Precio Compra (Incl. Impuestos)</x-form.label>
                                    <label class="flex items-center text-xs text-gray-500 cursor-pointer">
                                        <input type="radio" v-model="form.sunat_purchase_mode" value="total"> <span class="ml-1">Editar</span>
                                    </label>
                                </div>
                                <x-form.input.text 
                                    name="purchase_price_total" 
                                    id="purchase_price_total" 
                                    v-model="form.purchase_price_total" 
                                    v-bind:disabled="form.sunat_purchase_mode !== 'total'"
                                />
                            </div>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.group.switch name="enabled" label="{{ trans('general.enabled') }}" />

                @can('update-common-items')
                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons cancel-route="items.index" />
                    </x-slot>
                </x-form.section>
                @endcan
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script folder="common" file="items" />

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const taxMap = {!! $taxes->keyBy('id')->toJson() !!};

            let checkApp = setInterval(() => {
                const el = document.getElementById('item');
                if (el && el.__vue__) {
                    const vm = el.__vue__;
                    clearInterval(checkApp);

                    // Inicialización de estados para Edición
                    if (vm.form.sunat_sale_mode === undefined) vm.$set(vm.form, 'sunat_sale_mode', 'net');
                    if (vm.form.sunat_purchase_mode === undefined) vm.$set(vm.form, 'sunat_purchase_mode', 'net');
                    
                    const getMultiplier = () => {
                        let totalRate = 0;
                        const ids = vm.form.tax_ids || [];
                        ids.forEach(id => {
                            const t = taxMap[id];
                            if (t) {
                                if (t.type === 'normal' || t.type === 'inclusive') totalRate += parseFloat(t.rate);
                                else if (t.type === 'withholding') totalRate -= parseFloat(t.rate);
                            }
                        });
                        return 1 + (totalRate / 100);
                    };

                    const syncPrices = (type) => {
                        const mult = getMultiplier();
                        if (type === 'sale') {
                            if (vm.form.sunat_sale_mode === 'net') {
                                vm.$set(vm.form, 'sale_price_total', (parseFloat(vm.form.sale_price || 0) * mult).toFixed(2));
                            } else {
                                vm.$set(vm.form, 'sale_price', (parseFloat(vm.form.sale_price_total || 0) / mult).toFixed(2));
                            }
                        } else {
                            if (vm.form.sunat_purchase_mode === 'net') {
                                vm.$set(vm.form, 'purchase_price_total', (parseFloat(vm.form.purchase_price || 0) * mult).toFixed(2));
                            } else {
                                vm.$set(vm.form, 'purchase_price', (parseFloat(vm.form.purchase_price_total || 0) / mult).toFixed(2));
                            }
                        }
                    };

                    // Watchers profundos de Vue
                    vm.$watch('form.sale_price', () => { if (vm.form.sunat_sale_mode === 'net') syncPrices('sale'); });
                    vm.$watch('form.sale_price_total', () => { if (vm.form.sunat_sale_mode === 'total') syncPrices('sale'); });
                    vm.$watch('form.purchase_price', () => { if (vm.form.sunat_purchase_mode === 'net') syncPrices('purchase'); });
                    vm.$watch('form.purchase_price_total', () => { if (vm.form.sunat_purchase_mode === 'total') syncPrices('purchase'); });
                    vm.$watch('form.tax_ids', () => { syncPrices('sale'); syncPrices('purchase'); }, { deep: true });
                    vm.$watch('form.sunat_sale_mode', () => syncPrices('sale'));
                    vm.$watch('form.sunat_purchase_mode', () => syncPrices('purchase'));

                    // Cálculo inicial
                    syncPrices('sale');
                    syncPrices('purchase');
                }
            }, 100);
        });
    </script>
</x-layouts.admin>