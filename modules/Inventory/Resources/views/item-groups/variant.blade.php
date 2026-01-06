<x-table.tr class="relative flex items-start px-1 group/actions border-b" v-for="(variant_row, variant_index) in form.variants">
    <x-table.td class="w-3/12">
        <akaunting-select
            class="form-element-sm d-inline-block col-md-12"
            :name="'variants.' + variant_index + '.variant_id'"
            :data-field="'variants'"
            :options="{{ json_encode($variants) }}"
            :value="'{{ old('variant_id') }}'"
            @interface="variant_row.variant_id = $event"
            @change="getVariantsValue($event, variant_index)"
            :form-error="form.errors.get('variants.' + variant_index + '.variant_id')"
            :no-data-text="'{{ trans('general.no_data') }}'"
            :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
        ></akaunting-select>
    </x-table.td>

    <x-table.td class="w-8/12">
        <el-select
            class="form-element-sm d-inline-block col-md-12 h-100"
            :disabled="!selected_variants[variant_index].variant_values.length"
            v-model="form.variants[variant_index].variant_values"
            @change="onAddVariantValue($event, variant_index)"
            multiple
            filterable
            select-all
            @remove-tag="onDeleteVariantValue"
            placeholder="Select Value"
        >
            <el-option
                v-for="variant in selected_variants[variant_index].variant_values"
                :disabled="form.variants[variant_index].variant_values.includes(variant.value)"
                :key="variant.value"
                :label="variant.label"
                :value="variant.value">
            </el-option>
        </el-select>
        <div class="invalid-feedback d-block text-red mt-1"
            v-if="form.errors.has('variants.' + variant_index + '.variant_values')"
            v-html="form.errors.get('variants.' + variant_index + '.variant_values')">
        </div>
        <input name="fake" data-field="variants" type="hidden" v-model="variant_row.fake">
    </x-table.td>

    <x-table.td class="w-1/12 none-truncate" override="class">
        <x-button type="button" @click="onDeleteVariant(index)" class="px-3 py-1.5 mt-5 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50" override="class">
            <span class="w-full material-icons-outlined text-lg text-gray-300 group-hover:text-gray-500">delete</span>
        </x-button>
    </x-table.td>
</x-table.tr>