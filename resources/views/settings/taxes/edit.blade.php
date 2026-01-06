<x-layouts.admin>
    <x-slot name="title">
        {{ trans('general.title.edit', ['type' => trans_choice('general.tax_rates', 1)]) }}
    </x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="tax" method="PATCH" :route="['taxes.update', $tax->id]" :model="$tax">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}"
                            description="{{ trans('taxes.form_description.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="name" label="{{ trans('general.name') }}" />

                        <x-form.group.text name="rate" label="{{ trans('taxes.rate_percent') }}"
                            @input="onChangeTaxRate" />

                        <x-form.group.text name="sunat_code" label="Código SUNAT" :value="$tax->sunat_code"
                            placeholder="Ej. 1000 para IGV" />

                        <x-form.group.select name="type" label="{{ trans_choice('general.types', 1) }}"
                            :options="$types" :disabledOptions="$disable_options" />

                        <x-form.group.select name="apply_scope" label="Ámbito de Aplicación" :options="[
        'line' => 'Por Línea (afecta precio unitario - IGV, ISC)',
        'document' => 'Por Documento (afecta total factura - Detracción, Retención)',
        'fixed_unit' => 'Monto Fijo por Unidad (ICBPER)',
    ]" :selected="$tax->apply_scope ?? 'line'" />

                        <x-form.group.text name="priority" label="Prioridad" :value="$tax->priority" />
                    </x-slot>
                </x-form.section>

                <x-form.group.switch name="enabled" label="{{ trans('general.enabled') }}" />

                @can('update-settings-taxes')
                    <x-form.section>
                        <x-slot name="foot">
                            <x-form.buttons cancel-route="taxes.index" />
                        </x-slot>
                    </x-form.section>
                @endcan
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script folder="settings" file="taxes" />
</x-layouts.admin>