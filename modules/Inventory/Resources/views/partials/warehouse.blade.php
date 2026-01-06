<div class="bg-white rounded-lg shadow-lg p-4">
    <x-form.group.select 
        name="warehouse_id" 
        label="{{ trans_choice('inventory::general.warehouses', 1) }}" 
        :options="$warehouses" 
        form-group-class="sm:col-span-6 mb-2"
        :selected="$selected"
        data-item='warehouse_id'
        v-model='row.warehouse_id'
        visible-change='onBindingItemField(index, "warehouse_id")'
        dynamicOptions='this.item_warehouses[row.item_id]'
        model="{{ $input_model }}"
    />
</div>