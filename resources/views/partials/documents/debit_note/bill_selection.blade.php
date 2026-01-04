<div class="sm:col-span-2">
    <akaunting-select
        class="relative"
        id="bill_id"
        title="{{ trans_choice('general.bills', 1) }} (Obligatorio)"
        placeholder="- Seleccione Factura de Proveedor -"
        name="bill_id"
        :options="form.bills || []"
        :dynamic-options="form.bills || []"
        v-model="form.bill_id"
        @interface="form.bill_id = $event; window.onSelectReferenceBill($event)"
        clearable
        :not-required="false"
        loading-text="Cargando..."
        no-data-text="Seleccione un proveedor primero"
    >
        <div class="input-group-prepend absolute right-2 bottom-3 text-light-gray">
            <span class="input-group-text">
                <span class="material-icons w-4 h-5 text-sm">file_present</span>
            </span>
        </div>
    </akaunting-select>
</div>