<el-popover
    popper-class="p-0 h-0"
    placement="bottom"
    width="300"
    v-if="this.item_default_warehouse[row.item_id] != undefined"
    trigger="click"
>
    <div v-if="row.item_id == 0">
        @include('inventory::partials.create')
    </div>
    <div v-if="row.item_id != 0">
        @include('inventory::partials.warehouse')
    </div>

    <x-button
        type="button"
        class="relative absolute -top-2 flex items-center text-right border-0 p-0 pr-4 text-xs text-purple"
        slot="reference"
        override="class"
        v-if="row.item_id == 0"
    >
        <span class="border-b border-transparent transition-all hover:border-purple">
            {{ trans('inventory::general.add_warehouse') }}
        </span>
    </x-button>

    <x-button
        type="button"
        class="relative absolute -top-2 flex items-center text-right border-0 p-0 pr-4 text-xs text-purple"
        slot="reference"
        override="class"
        v-if="row.item_id != 0"
    >
        <span class="border-b border-transparent transition-all hover:border-purple">
            {{ trans('inventory::general.edit_warehouse') }}
        </span>
    </x-button>
</el-popover>
