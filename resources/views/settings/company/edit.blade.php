<x-layouts.admin>
    <x-slot name="title">{{ trans_choice('general.companies', 1) }}</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="setting" method="PATCH" route="settings.company.update">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.general') }}"
                            description="{{ trans('settings.company.form_description.general') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <div class="sm:col-span-3 grid gap-x-8 gap-y-6 grid-rows-4">
                            <x-form.group.text name="tax_number" label="RUC"
                                value="{{ setting('company.tax_number') }}" />

                            <x-form.group.text name="name" label="{{ trans('settings.company.name') }}"
                                value="{{ setting('company.name') }}" />

                            <x-form.group.text name="email" label="{{ trans('settings.company.email') }}"
                                value="{{ setting('company.email') }}" />

                            <x-form.group.text name="phone" label="{{ trans('settings.company.phone') }}"
                                value="{{ setting('company.phone') }}" not-required />

                            <x-form.group.text name="sunat_bn_account" label="Cuenta Banco de la Nación (SPOT)"
                                value="{{ setting('company.sunat_bn_account') }}" not-required />
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.group.file name="logo" label="{{ trans('settings.company.logo') }}"
                                :value="setting('company.logo')" not-required />
                            <div id="logo-dimension-warning"
                                class="hidden mt-2 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                                <strong>⚠️ Advertencia:</strong> El logo excede las dimensiones recomendadas para
                                facturas (máximo 300x100 píxeles).
                                Por favor, redimensiona o recorta la imagen para un mejor resultado en la impresión de
                                documentos.
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Tamaño recomendado: máximo 300px de ancho x 100px de
                                alto</p>
                        </div>
                    </x-slot>
                </x-form.section>

                @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Buscar el input de archivo del logo
                            const logoInput = document.querySelector('input[name="logo"]');
                            const warningDiv = document.getElementById('logo-dimension-warning');

                            if (logoInput && warningDiv) {
                                logoInput.addEventListener('change', function (e) {
                                    const file = e.target.files[0];
                                    if (file && file.type.startsWith('image/')) {
                                        const img = new Image();
                                        img.onload = function () {
                                            const maxWidth = 300;
                                            const maxHeight = 100;

                                            if (this.width > maxWidth || this.height > maxHeight) {
                                                warningDiv.classList.remove('hidden');
                                                warningDiv.innerHTML = '<strong>⚠️ Advertencia:</strong> El logo (' + this.width + 'x' + this.height + 'px) excede las dimensiones recomendadas para facturas (máximo ' + maxWidth + 'x' + maxHeight + ' píxeles). Por favor, redimensiona o recorta la imagen para un mejor resultado en la impresión de documentos.';
                                            } else {
                                                warningDiv.classList.add('hidden');
                                            }
                                            URL.revokeObjectURL(img.src);
                                        };
                                        img.src = URL.createObjectURL(file);
                                    }
                                });
                            }
                        });
                    </script>
                @endpush

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head title="{{ trans('general.address') }}"
                            description="{{ trans('settings.company.form_description.address') }}" />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.textarea name="address" label="{{ trans('settings.company.address') }}"
                            :value="setting('company.address')" not-required />

                        <x-form.group.text name="city" label="{{ trans_choice('general.cities', 1) }}"
                            value="{{ setting('company.city') }}" not-required />

                        <x-form.group.text name="zip_code" label="{{ trans('general.zip_code') }}"
                            value="{{ setting('company.zip_code') }}" not-required />

                        <x-form.group.text name="state" label="{{ trans('general.state') }}"
                            value="{{ setting('company.state') }}" not-required />

                        <x-form.group.country />
                    </x-slot>
                </x-form.section>

                @can('update-settings-company')
                    <x-form.section>
                        <x-slot name="foot">
                            <x-form.buttons :cancel="url()->previous()" />
                        </x-slot>
                    </x-form.section>
                @endcan

                <x-form.input.hidden name="_prefix" value="company" />
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script folder="settings" file="settings" />
</x-layouts.admin>