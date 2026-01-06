<x-layouts.admin>
    <x-slot name="title">{{ trans('inventory::general.name') }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="setting" method="POST" route="inventory.settings.update">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('inventory::general.warehouses', 1) }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.select name="default_warehouse" label="{{ trans('inventory::warehouses.default') }}" :options="$warehouses" :selected="old('default_warehouse', setting('inventory.default_warehouse'))"/>

                        <x-form.group.toggle name="track_inventory" label="{{ trans('inventory::general.track_inventory') }}" :value="old('track_inventory', setting('inventory.track_inventory'))" not-required form-group-class="sm:col-span-6" />

                        <x-form.group.toggle name="negative_stock" label="{{ trans('inventory::general.negative_stock') }}" :value="old('negative_stock', setting('inventory.negative_stock'))" not-required form-group-class="sm:col-span-6" />

                        <x-form.group.toggle name="reorder_level_notification" label="{{ trans('inventory::items.reorder_level_notification') }}" :value="old('reorder_level_notification', setting('inventory.reorder_level_notification'))" not-required form-group-class="sm:col-span-6" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('inventory::general.barcode') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.select 
                            name="barcode_type" 
                            label="{{ trans('inventory::settings.barcode_type') }}" 
                            :options="$barcode_types" 
                            :selected="old('barcode_type', setting('inventory.barcode_type'))" 
                            change="exampleBarcode($event)"
                        />

                        <div class="sm:col-span-3 truncate">
                            <label>{{ trans('inventory::settings.example') }}</label>
                            <div v-html="ex_barcode"></div>
                            <label v-html="ex_barcode_label"></label>
                        </div>

                        <div class="sm:col-span-2 rounded-lg cursor-pointer text-center py-2 px-2">
                            <label class="cursor-pointer">
                                <div @click="form.barcode_print_template='single'">
                                    <img src="{{ asset('modules/Inventory/Resources/assets/img/barcode/print_templates/single.png') }}" class="h-60 my-3" alt="Single" />
                                    <input type="radio" name="barcode_print_template" value="single" v-model="form._barcode_print_template">
                                    {{ trans('inventory::settings.single') }}
                                </div>
                            </label>
                        </div>

                        <div class="sm:col-span-2 rounded-lg cursor-pointer text-center py-2 px-2">
                            <label class="cursor-pointer">
                                <div @click="form.barcode_print_template='double'">
                                    <img src="{{ asset('modules/Inventory/Resources/assets/img/barcode/print_templates/double.png') }}" class="h-60 my-3" alt="Double" />
                                    <input type="radio" name="barcode_print_template" value="double" v-model="form._barcode_print_template">
                                    {{ trans('inventory::settings.double') }}
                                </div>
                            </label>
                        </div>

                        <div class="sm:col-span-2 rounded-lg cursor-pointer text-center py-2 px-2">
                            <label class="cursor-pointer">
                                <div @click="form.barcode_print_template='triple'">
                                    <img src="{{ asset('modules/Inventory/Resources/assets/img/barcode/print_templates/triple.png') }}" class="h-60 my-3" alt="Triple" />
                                    <input type="radio" name="barcode_print_template" value="triple" v-model="form._barcode_print_template">
                                    {{ trans('inventory::settings.triple') }}
                                </div>
                            </label>
                        </div>

                        <x-form.input.hidden name="_barcode_print_template" :value="setting('inventory.barcode_print_template')" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('inventory::general.transfer_orders', 1) }}" description="" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="transfer_order_prefix" label="{{ trans('inventory::settings.number.prefix') }}" value="{{ old('transfer_order_prefix', setting('inventory.transfer_order_prefix')) }}" />

                        <x-form.group.text name="transfer_order_digit" label="{{ trans('inventory::settings.number.digit') }}" value="{{ old('transfer_order_digit', setting('inventory.transfer_order_digit')) }}" />

                        <x-form.group.text name="transfer_order_next" label="{{ trans('inventory::settings.number.next') }}" value="{{ old('transfer_order_next', setting('inventory.transfer_order_next')) }}" />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans_choice('inventory::general.adjustments', 1) }}" description="" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="adjustment_prefix" label="{{ trans('inventory::settings.number.prefix') }}" value="{{ old('adjustment_prefix', setting('inventory.adjustment_prefix')) }}" />

                        <x-form.group.text name="adjustment_digit" label="{{ trans('inventory::settings.number.digit') }}" value="{{ old('adjustment_digit', setting('inventory.adjustment_digit')) }}" />

                        <x-form.group.text name="adjustment_next" label="{{ trans('inventory::settings.number.next') }}" value="{{ old('adjustment_next', setting('inventory.adjustment_next')) }}" />
                    </x-slot>
                </x-form.section>

                <div class="grid sm:grid-cols-6 mr-4">
                    <x-form.section class="sm:col-span-3 mb-14" override="class">
                        <x-slot name="head">
                            <x-form.section.head title="{{ trans('inventory::transferorders.reason') }}" description="" />
                        </x-slot>

                        <x-slot name="body">
                            <div class="sm:col-span-6 overflow-x-scroll large-overflow-unset">
                                @include('inventory::settings.reason')
                            </div>
                        </x-slot>
                    </x-form.section>

                    <x-form.section class="sm:col-span-3 mb-14 ml-4" override="class">
                        <x-slot name="head">
                            <x-form.section.head title="{{ trans('inventory::general.unit') }}" description="" />
                        </x-slot>

                        <x-slot name="body">
                            <div class="sm:col-span-6 overflow-x-scroll large-overflow-unset">
                                @include('inventory::settings.unit')
                            </div>
                        </x-slot>
                    </x-form.section>
                </div>

                @can('update-inventory-settings')
                    <x-form.section>
                        <x-slot name="foot">
                            <x-form.buttons :cancel="url()->previous()" />
                        </x-slot>
                    </x-form.section>
                @endcan
            </x-form>
        </x-form.container>
    </x-slot>

    @push('stylesheet')
        <style type="text/css">
            .unit-checkbox::before {
                left: 0 !important;
                right: 0 !important;
                margin: 0 auto !important;
            }
            .unit-checkbox::after {
                left: 0 !important;
            }
        </style>
    @endpush

    @push('scripts_start')
        <script type="text/javascript">
            var unit_items = {!! json_encode($items) !!};
            var reason_items = {!! json_encode($reasons) !!};
        </script>
    @endpush

    <x-script alias="inventory" file="settings" />
</x-layouts.admin>
