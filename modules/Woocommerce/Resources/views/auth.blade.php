<x-layouts.admin>
    <x-slot name="title">{{ trans('woocommerce::general.name') }}</x-slot>

    <x-slot name="content">
        <div class="notifications">
            <div class="w-full lg:w-8/12 flex items-center ltr:right-4 rtl:left-4 py-2 px-4 font-bold text-sm my-5 rounded-lg bg-red-100 text-red-600" role="alert">
                <strong>{!! trans('woocommerce::general.form.auth_warning') !!}</strong>
            </div>
        </div>

        <x-form.container>
            <x-form id="woocommerce" method="POST" route="woocommerce.auth.redirect">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}" description="{{ trans('woocommerce::general.description') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text name="url" label="{{ trans('woocommerce::general.form.url') }}" placeholder="wordpress.com" :value="old('url', setting('woocommerce.url'))" />
                        <div class="relative sm:col-span-6">{{ trans('woocommerce::general.form.auth_description') }}</div>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <div class="flex items-center justify-end sm:col-span-6">
                            <x-link
                                href="{{ route('woocommerce.edit', ['type' => 'custom']) }}"
                                class="px-3 py-1.5 sm:mb-0 rounded-xl text-sm font-medium leading-6 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50"
                                override="class"
                            >
                                {{ trans('woocommerce::general.form.custom_authentication') }}
                            </x-link>

                            <x-button
                                type="submit"
                                class="ml-2 relative flex items-center justify-center bg-green hover:bg-green-700 text-white px-6 py-1.5 text-base rounded-lg disabled:bg-green-100"
                                ::disabled="form.loading"
                                override="class"
                            >
                                <i v-if="form.loading" class="animate-submit delay-[0.28s] absolute w-3 h-3 rounded-full left-0 right-0 -top-3.5 m-auto before:absolute before:w-2 before:h-2 before:rounded-full before:animate-submit before:delay-[0.14s] after:absolute after:w-2 after:h-2 after:rounded-full after:animate-submit before:-left-3.5 after:-left-3.5 after:delay-[0.42s]"></i>
                                <span :class="[{'opacity-0': form.loading}]">
                                    {{ trans('woocommerce::general.form.authenticate') }}
                                </span>
                            </x-button>
                        </div>
                    </x-slot>
                </x-form.section>
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script alias="woocommerce" file="woocommerce" />
</x-layouts.admin>
