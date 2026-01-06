<x-layouts.admin>
    <x-slot name="title">{{ trans('general.title.new', ['type' => trans_choice('inventory::general.warehouses', 1)]) }}</x-slot>

    <x-slot name="favorite"
        title="{{ trans('general.title.new', ['type' => trans_choice('inventory::general.warehouses', 1)]) }}"
        icon="warehouses"
        route="inventory.warehouses.create"
    ></x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="warehouse" route="inventory.warehouses.store">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="name" label="{{ trans('general.name') }}" />

                        <x-form.group.text name="email" label="{{ trans('general.email') }}" not-required />

                        <x-form.group.text name="phone" label="{{ trans('general.phone') }}" not-required />

                        <x-form.group.textarea name="address" label="{{ trans('general.address') }}" v-model="form.address" not-required />

                        <x-form.group.text name="city" label="{{ trans_choice('general.cities', 1) }}" not-required />

                        <x-form.group.text name="zip_code" label="{{ trans('general.zip_code') }}" not-required />

                        <x-form.group.text name="state" label="{{ trans('general.state') }}" not-required />

                        <x-form.group.select name="country" label="{{ trans_choice('general.countries', 1) }}" :options="trans('countries')" :selected="setting('company.country')" not-required autocomplete="off" />

                        <x-form.group.textarea name="description" label="{{ trans('general.description') }}" not-required/>

                        <x-form.group.toggle name="default_warehouse" label="{{ trans('inventory::general.default_warehouse') }}" value=false form-group-class="sm:col-span-6" />
                    
                        <x-form.input.hidden name="enabled" value=1 />
                    </x-slot>
                </x-form.section>

                @stack('create_warehouse_users_start')

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons cancel-route="inventory.warehouses.index" />
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script alias="inventory" file="warehouses" />
</x-layouts.admin>

