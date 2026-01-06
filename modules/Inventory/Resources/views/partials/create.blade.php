<div class="bg-white rounded-lg shadow-lg p-4">
    <x-form.group.select 
        name="warehouse_id" 
        label="{{ trans_choice('inventory::general.warehouses', 1) }}" 
        :options="$warehouses" 
        :selected="setting('inventory.default_warehouse')" 
        form-group-class="sm:col-span-6 mb-2"
        data-item="warehouse_id"
        v-model="row.warehouse_id"
        visible-change="onBindingItemField(index, 'warehouse_id')" 
    />

    <x-form.group.select 
        name="unit" 
        label="{{ trans('inventory::general.unit') }}" 
        :options="$units" 
        :selected="setting('inventory.default_unit')" 
        form-group-class="sm:col-span-6 mb-2"
        data-item="unit"
        v-model="row.unit"
        visible-change="onBindingItemField(index, 'unit')" 
    />

    <x-form.group.checkbox 
        name="track_inventory" 
        id="track_inventory" 
        :options="['track_inventory' => trans('inventory::items.track_inventory')]" 
        form-group-class="sm:col-span-6" 
        checkbox-class="sm:col-span-6"
        v-model="row.track_inventory"
    />
</div>