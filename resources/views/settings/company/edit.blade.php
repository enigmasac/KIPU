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
                    <style>
                        /* Logo preview styles - show full logo at natural size, centered */
                        .logo-dropzone-container .dropzone {
                            height: auto !important;
                            min-height: 120px !important;
                            width: auto !important;
                            min-width: 200px !important;
                            max-width: 400px !important;
                            overflow: visible !important;
                        }

                        .logo-dropzone-container .dropzone.dz-max-files-reached {
                            height: auto !important;
                            overflow: visible !important;
                        }

                        .logo-dropzone-container .dz-preview,
                        .logo-dropzone-container .dz-preview-single {
                            position: relative !important;
                            width: auto !important;
                            height: auto !important;
                            min-height: 80px !important;
                            display: block !important;
                            overflow: visible !important;
                            text-align: center !important;
                        }

                        .logo-dropzone-container .dz-preview-cover {
                            position: relative !important;
                            width: auto !important;
                            height: auto !important;
                            top: auto !important;
                            left: auto !important;
                            right: auto !important;
                            bottom: auto !important;
                            overflow: visible !important;
                            display: inline-block !important;
                            padding: 10px !important;
                        }

                        .logo-dropzone-container .dz-preview-img {
                            object-fit: contain !important;
                            width: auto !important;
                            height: auto !important;
                            max-width: 300px !important;
                            max-height: 100px !important;
                            background-color: transparent !important;
                            border-radius: 0 !important;
                            display: block !important;
                        }

                        .logo-dropzone-container .dz-image-preview {
                            overflow: visible !important;
                            width: auto !important;
                            height: auto !important;
                            display: inline-block !important;
                        }

                        /* Hide delete button repositioning issues */
                        .logo-dropzone-container [data-dz-remove] {
                            position: relative !important;
                        }
                    </style>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Add logo-specific class to the logo dropzone container
                            const logoLabel = document.querySelector('label[for="logo"]');
                            if (logoLabel) {
                                const container = logoLabel.closest('.sm\\:col-span-3');
                                if (container) {
                                    container.classList.add('logo-dropzone-container');
                                }
                            }

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