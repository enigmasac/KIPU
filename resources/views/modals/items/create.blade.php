@php
    $sunat_units = config('peru-core.sunat.units', [['id' => 'NIU', 'name' => 'NIU (UNIDAD)']]);
    $sunat_units_options = collect($sunat_units)->pluck('name', 'id');

    $sunat_tax_types = config('peru-core.sunat.tax_types', [['id' => '10', 'name' => 'Gravado - Operación Onerosa']]);
    $sunat_tax_types_options = collect($sunat_tax_types)->pluck('name', 'id');
@endphp
<x-form id="item" route="items.store">
    <div class="grid sm:grid-cols-6 gap-x-8 gap-y-6 my-3.5">
        <x-form.group.text name="name" label="{{ trans('general.name') }}" />

        <x-form.group.text name="sku" label="{{ trans('invoices.sku') }}" />

        <x-form.group.select name="sunat_unit_code" label="{{ trans('invoices.unit') }}" :options="$sunat_units_options" selected="NIU" />

        <x-form.group.select name="sunat_tax_type" label="Afectación IGV" :options="$sunat_tax_types_options" selected="10" />

        <x-form.group.tax name="tax_ids" multiple not-required without-add-new />

        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" not-required />

        <x-form.group.text name="sale_price" label="{{ trans('items.sale_price') }}" />

        <x-form.group.text name="purchase_price" label="{{ trans('items.purchase_price') }}" />

        <x-form.group.category type="item" not-required without-add-new />

        <x-form.input.hidden name="enabled" value="1" />
    </div>
</x-form>
