<x-form.accordion type="company" :open="(! $hideLogo && empty(setting('company.logo')))">
    <x-slot name="head">
        <x-form.accordion.head
            title="{{ trans_choice($textSectionCompaniesTitle, 1) }}"
            description="{{ trans($textSectionCompaniesDescription) }}"
        />
    </x-slot>

    <x-slot name="body">
        <div class="sm:col-span-3 flex items-center space-x-4">
            @if (setting('company.logo'))
                <img src="{{ Storage::url(setting('company.logo')) }}" class="h-20 w-auto" alt="Logo" />
            @endif
            <div>
                <h3 class="text-lg font-medium text-black">{{ setting('company.name') }}</h3>
                <p class="text-sm text-gray-500">{{ setting('company.tax_number') }}</p>
                <p class="text-sm text-gray-500">
                    {!! nl2br(e(setting('company.address'))) !!}
                    <br>
                    @if (setting('company.city'))
                        {{ setting('company.city') }},
                    @endif
                    @if (setting('company.state'))
                        {{ setting('company.state') }}
                    @endif
                    @if (setting('company.zip_code'))
                        {{ setting('company.zip_code') }}
                    @endif
                    @if (setting('company.country'))
                        <br>
                        {{ trans('countries.' . setting('company.country')) }}
                    @endif
                </p>
            </div>
        </div>
    </x-slot>
</x-form.accordion>
