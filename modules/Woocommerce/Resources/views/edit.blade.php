<x-layouts.admin>
    <x-slot name="title">{{ trans('woocommerce::general.name') }}</x-slot>

    <x-slot name="content">
        @if(false === $apiConnectionOk)
            <div class="notifications">
                <div class="w-full lg:w-8/12 flex items-center ltr:right-4 rtl:left-4 py-2 px-4 font-bold text-sm my-5 rounded-lg bg-red-100 text-red-600" role="alert">
                    <strong>{!! trans('woocommerce::general.error.api_connection_error') !!}</strong>
                </div>
            </div>
        @elseif(false === $customFieldsInstalled)
            <div class="notifications">
                <div class="w-full lg:w-8/12 flex items-center ltr:right-4 rtl:left-4 py-2 px-4 font-bold text-sm my-5 rounded-lg bg-blue-100 text-blue-600" role="alert">
                    <strong>{!! $customFieldsMessage !!}</strong>
                </div>
            </div>
        @endif

        @if($runningBackgroundMessage)
            <div class="notifications">
                <div class="w-full lg:w-8/12 flex items-center ltr:right-4 rtl:left-4 py-2 px-4 font-bold text-sm my-5 rounded-lg bg-orange-100 text-orange-600" role="alert">
                    <strong>{!! $runningBackgroundMessage !!}</strong>
                </div>
            </div>
        @endif

        <x-form.container>
            <x-form id="woocommerce" method="POST" route="woocommerce.edit">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" description="{{ trans('woocommerce::general.description') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="url" label="{{ trans('woocommerce::general.form.url') }}" placeholder="wordpress.com" :value="old('url', setting('woocommerce.url'))" />

                        @if (empty(setting('woocommerce.consumer_key')))
                            <x-form.group.text name="consumer_key" label="{{ trans('woocommerce::general.form.consumer_key') }}" :value="old('consumer_key', setting('woocommerce.consumer_key'))" />
                        @else
                            <x-form.input.hidden name="consumer_key" :value="setting('woocommerce.consumer_key')" />
                        @endif

                        @if (empty(setting('woocommerce.consumer_secret')))
                            <x-form.group.text name="consumer_secret" label="{{ trans('woocommerce::general.form.consumer_secret') }}" :value="old('consumer_secret', setting('woocommerce.consumer_secret'))" />
                        @else
                            <x-form.input.hidden name="consumer_secret" :value="setting('woocommerce.consumer_secret')" />
                        @endif

                        <x-form.group.select
                            multiple
                            name="order_status_ids"
                            label="{{ trans('woocommerce::general.form.order_status_ids') }}"
                            :options="$orderStatuses"
                            :selected="json_decode(setting('woocommerce.order_status_ids'))"
                            not-required />

                        <x-form.group.select
                            name="invoice_category_id"
                            label="{{ trans('woocommerce::general.form.invoices_category') }}"
                            :options="$invoiceCategories"
                            :selected="setting('woocommerce.invoice_category_id')"
                            not-required />

                        <x-form.group.toggle name="two_way_create_update" label="{{ trans('woocommerce::general.form.two_way_create_update') }}" :value="old('two_way_create_update', setting('woocommerce.two_way_create_update'))" not-required />

                        <x-form.group.toggle name="two_way_delete" label="{{ trans('woocommerce::general.form.two_way_delete') }}" :value="old('two_way_delete', setting('woocommerce.two_way_delete', 0))" not-required />

                        <x-form.input.hidden name="type" value="custom" />
                    </x-slot>
                </x-form.section>

                <div class="mb-14">
                    <x-form.section.head title="{{ trans('woocommerce::general.form.field_mapping') }}" description="" />

                    <x-table>
                        <x-table.thead>
                            <x-table.tr class="flex items-center px-1">
                                <x-table.th class="w-6/12" >
                                    {{ trans('woocommerce::general.form.wp_fields') }}
                                </x-table.th>

                                <x-table.th class="w-6/12" >
                                    {{ trans('woocommerce::general.form.fields') }}
                                </x-table.th>
                            </x-table.tr>
                        </x-table.thead>

                        <x-table.tbody id="invoice-item-rows">
                            <x-table.tr v-for="(row, index) in form.items" class="relative flex items-center border-b hover:bg-gray-100 px-1 group">
                                <x-table.th class="w-5/12">
                                    <akaunting-select
                                        class=""
                                        :icon="''"
                                        :title="''"
                                        :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('woocommerce::general.form.wp_fields', 1)]) }}'"
                                        :name="'field_mapping.' + index + '.wp_field'"
                                        :options="{{ json_encode($wpFields) }}"
                                        :value="row.wp_field"
                                        :multiple="false"
                                        :collapse="true"
                                        @interface="row.wp_field = $event"
                                        :no-data-text="'{{ trans('general.no_data') }}'"
                                        :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                                    ></akaunting-select>
                                    <input name="field_mapping[][wp_field]" type="hidden" data-item="wp_field" v-model="row.wp_field">
                                </x-table.th>

                                <x-table.th class="w-1/12" hidden-mobile>
                                    <x-icon icon="swap_horiz" class="ml-1.5" />
                                </x-table.th>

                                <x-table.th class="w-5/12">
                                    <akaunting-select
                                        class="mb-0 select-tax"
                                        :icon="''"
                                        :title="''"
                                        :placeholder="'{{ trans('general.form.select.field', ['field' => trans_choice('woocommerce::general.form.fields', 1)]) }}'"
                                        :name="'field_mapping.' + index + '.field_id'"
                                        :options="{{ json_encode($fields) }}"
                                        :value="row.field_id"
                                        :multiple="false"
                                        :collapse="false"
                                        @interface="row.field_id = $event"
                                        :no-data-text="'{{ trans('general.no_data') }}'"
                                        :no-matching-data-text="'{{ trans('general.no_matching_data') }}'"
                                    ></akaunting-select>
                                    <input name="field_mapping[][field_id]" type="hidden" data-item="field_id" v-model="row.field_id">
                                </x-table.th>

                                <x-table.th class="w-1/12 sm:mt-5">
                                    <x-button
                                        type="button"
                                        title="{{ trans('general.delete') }}"
                                        @click="onDeleteFieldMapping(index)"
                                        class="px-3 py-1.5 mb-3 sm:mt-2 sm:mb-0 rounded-xl text-sm font-medium leading-6 hover:bg-gray-200 disabled:bg-gray-50"
                                        override="class">
                                        <x-icon icon="delete" class="w-full text-lg text-gray-300 group-hover:text-gray-500" />
                                    </x-button>
                                </x-table.th>
                            </x-table.tr>

                            <x-table.tr id="addItem">
                                <x-table.td class="w-full whitespace-nowrap text-sm font-normal text-black truncate ltr:pr-6 rtl:pl-6 ltr:text-left rtl:text-right cursor-pointer" override="class">
                                    <x-button type="button" id="button-add-item" override="class" @click="onAddCustomField" class="w-full py-4 text-secondary flex items-center justify-center" title="{{ trans('general.add') }}">
                                        <x-icon icon="add" /> {{ trans('woocommerce::general.form.new_field') }}
                                    </x-button>
                                </x-table.td>
                            </x-table.tr>
                        </x-table.tbody>
                    </x-table>
                </div>

                <x-form.section>
                    <x-slot name="foot">
                        @mobile
                            <div class="flex items-center justify-end sm:col-span-6 mb-5">
                                <x-link
                                    href="{{ route('woocommerce.auth.show') }}"
                                    :disabled="false === $apiConnectionOk"
                                    class="px-6 py-1.5 bg-teal-100 text-teal-600 rounded-lg ltr:mr-2 rtl:ml-2"
                                    override="class"
                                >
                                    &nbsp; {{ trans('woocommerce::general.form.' . is_null(setting('woocommerce.url')) ? 're-authenticate' : 'authenticate') }}
                                </x-link>

                                <x-button
                                    @click="sync"
                                    class="px-6 py-1.5 bg-blue-100 text-blue-600 rounded-lg"
                                    override="class"
                                >
                                    {{ trans('woocommerce::general.form.sync') }}
                                </x-button>
                            </div>
                        @endmobile

                        <div class="flex items-center justify-end sm:col-span-6">
                            @notmobile
                                <x-link
                                    href="{{ route('woocommerce.auth.show') }}"
                                    :disabled="false === $apiConnectionOk"
                                    class="px-6 py-1.5 bg-teal-100 text-teal-600 rounded-lg ltr:mr-2 rtl:ml-2"
                                    override="class"
                                >
                                    &nbsp; {{ trans('woocommerce::general.form.' . is_null(setting('woocommerce.url')) ? 're-authenticate' : 'authenticate') }}
                                </x-link>

                                <x-button
                                    @click="sync"
                                    class="px-6 py-1.5 bg-blue-100 text-blue-600 rounded-lg ltr:mr-2 rtl:ml-2"
                                    override="class"
                                >
                                    {{ trans('woocommerce::general.form.sync') }}
                                </x-button>
                            @endnotmobile

                            <x-link
                                href="{{ url()->previous() }}"
                                class="px-6 py-1.5 hover:bg-gray-200 rounded-lg ltr:mr-2 rtl:ml-2"
                                override="class"
                            >
                                {{ trans('general.cancel') }}
                            </x-link>

                            <x-button
                                type="submit"
                                class="relative flex items-center justify-center bg-green hover:bg-green-700 text-white px-6 py-1.5 text-base rounded-lg disabled:bg-green-100"
                                ::disabled="form.loading"
                                override="class"
                            >
                                <i v-if="form.loading" class="animate-submit delay-[0.28s] absolute w-3 h-3 rounded-full left-0 right-0 -top-3.5 m-auto before:absolute before:w-2 before:h-2 before:rounded-full before:animate-submit before:delay-[0.14s] after:absolute after:w-2 after:h-2 after:rounded-full after:animate-submit before:-left-3.5 after:-left-3.5 after:delay-[0.42s]"></i>
                                <span :class="[{'opacity-0': form.loading}]">
                                    {{ trans('general.save') }}
                                </span>
                            </x-button>
                        </div>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>

        <akaunting-modal
            :show="show">
            <template #card-header>
                <h4 class="text-base font-medium">
                    {{ trans('woocommerce::general.form.sync') }}
                </h4>

                <button v-show="show_close" type="button" class="text-lg" @click="show = show_close = false" aria-hidden="true">
                    <span class="rounded-md border-b-2 px-2 py-1 text-sm bg-gray-100">esc</span>
                </button>
            </template>

                <template #modal-body>
                    <div class="py-1 px-5 bg-body h-5/6 overflow-y-auto">
                        <el-progress :text-inside="true" :stroke-width="24" :percentage="progress_total" :status="status"></el-progress>
                        <div class="mt-3" id="progress-text" v-html="html"></div>

                        <el-progress class="border-t pt-5 mt-5" :text-inside="true" :stroke-width="24" :percentage="progress_totalAkaunting" :status="statusAkaunting"></el-progress>
                        <div class="mt-3" id="progress-text" v-html="htmlAkaunting"></div>
                    </div>
                </template>

                <template #card-footer>
                    <span></span>
                </template>
        </akaunting-modal>
    </x-slot>

    @push('scripts_start')
        <script type="text/javascript">
            var field_mapping = {!! setting('woocommerce.field_mapping', 'false') !!};
        </script>
    @endpush

    <x-script alias="woocommerce" file="woocommerce" />
</x-layouts.admin>
