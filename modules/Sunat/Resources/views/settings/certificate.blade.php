<x-layouts.admin>
    <x-slot name="title">{{ trans('Certificados SUNAT') }}</x-slot>

    <x-slot name="favorite"
            title="{{ trans('Certificados SUNAT') }}"
            icon="certificate"
            route="sunat.configuration.certificate"
    ></x-slot>

    <x-slot name="content">
        <x-form.container>
            {{-- Formulario de Upload --}}
            <form id="certificate-upload" method="POST" action="{{ route('sunat.configuration.certificate.upload') }}" enctype="multipart/form-data">
                @csrf
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head 
                            title="Subir Certificado Digital" 
                            description="Suba su certificado digital (.pfx o .p12) con su contraseña. El nombre se extraerá automáticamente del archivo." 
                            class="mb-0"
                        />
                    </x-slot>

                    <x-slot name="body">
                        <div class="sm:col-span-6">
                            <label for="certificate_file" class="block text-sm font-medium text-gray-700 mb-1">
                                Archivo de Certificado (.pfx o .p12) <span class="text-red-500">*</span>
                            </label>
                            <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-purple-500 transition-colors cursor-pointer">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="certificate_file" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                            <span>Seleccionar archivo</span>
                                            <input id="certificate_file" name="certificate_file" type="file" class="sr-only" accept=".pfx,.p12" required>
                                        </label>
                                        <p class="pl-1">o arrastra aquí</p>
                                    </div>
                                    <p class="text-xs text-gray-500">Archivos .pfx o .p12</p>
                                </div>
                            </div>
                            <p id="file-name" class="mt-2 text-sm text-gray-500"></p>
                            @error('certificate_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-6">
                            <label for="certificate_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña del Certificado <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="certificate_password" id="certificate_password" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm" placeholder="Ingrese la contraseña de su certificado" required>
                            @error('certificate_password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-6">
                            <x-alert type="info">
                                <strong>Información:</strong> El certificado digital es necesario para firmar los comprobantes electrónicos. 
                                El nombre del certificado se extraerá automáticamente del archivo.
                            </x-alert>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('sunat.configuration.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Guardar
                            </button>
                        </div>
                    </x-slot>
                </x-form.section>
            </form>

            {{-- Lista de Certificados --}}
            <x-form.section>
                <x-slot name="head">
                    <x-form.section.head 
                        title="Certificados Registrados" 
                        description="Administre los certificados digitales de su empresa" 
                    />
                </x-slot>

                <x-slot name="body">
                    @if($certificates->isEmpty())
                        <div class="sm:col-span-6">
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay certificados registrados</h3>
                                <p class="mt-1 text-sm text-gray-500">Suba su primer certificado digital usando el formulario de arriba para comenzar a emitir comprobantes electrónicos.</p>
                            </div>
                        </div>
                    @else
                        <div class="sm:col-span-6">
                            <div class="overflow-hidden rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Certificado
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Emisor
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Expiración
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado
                                            </th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Acciones</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($certificates as $cert)
                                            <tr class="{{ $cert->isExpired() ? 'bg-red-50' : '' }}">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $cert->name }}
                                                                @if($cert->is_active)
                                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                        Activo
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $cert->subject }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $cert->issuer }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $cert->expires_at?->format('d/m/Y') }}
                                                    </div>
                                                    @if($cert->isExpiringSoon() && !$cert->isExpired())
                                                        <div class="text-xs text-orange-600">
                                                            Expira pronto
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($cert->isExpired())
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                                <circle cx="4" cy="4" r="3" />
                                                            </svg>
                                                            Expirado
                                                        </span>
                                                    @elseif($cert->isExpiringSoon())
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                                <circle cx="4" cy="4" r="3" />
                                                            </svg>
                                                            Por expirar
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                                <circle cx="4" cy="4" r="3" />
                                                            </svg>
                                                            Válido
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <x-delete-button 
                                                        :model="$cert"
                                                        route="sunat.configuration.certificate.delete"
                                                        text="¿Está seguro de eliminar este certificado?"
                                                    />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </x-slot>
            </x-form.section>
        </x-form.container>
    </x-slot>
    @push('scripts_end')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('certificate_file');
                const fileNameDisplay = document.getElementById('file-name');
                const dropZone = document.getElementById('drop-zone');

                console.log('Script de certificado cargado');

                if (fileInput && fileNameDisplay) {
                    fileInput.addEventListener('change', function(e) {
                        console.log('Cambio en file input detectado');
                        if (this.files && this.files.length > 0) {
                            const name = this.files[0].name;
                            fileNameDisplay.innerHTML = `<strong>Archivo seleccionado:</strong> ${name}`;
                            fileNameDisplay.classList.remove('text-gray-500');
                            fileNameDisplay.classList.add('text-purple-600');
                        }
                    });
                }

                if (dropZone && fileInput) {
                    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                        dropZone.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                        }, false);
                    });

                    ['dragenter', 'dragover'].forEach(eventName => {
                        dropZone.addEventListener(eventName, () => {
                            dropZone.classList.add('border-purple-500', 'bg-purple-50');
                        }, false);
                    });

                    ['dragleave', 'drop'].forEach(eventName => {
                        dropZone.addEventListener(eventName, () => {
                            dropZone.classList.remove('border-purple-500', 'bg-purple-50');
                        }, false);
                    });

                    dropZone.addEventListener('drop', (e) => {
                        console.log('Archivo soltado en drop-zone');
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        
                        if (files.length > 0) {
                            fileInput.files = files;
                            // Disparar evento change manualmente
                            const event = new Event('change', { bubbles: true });
                            fileInput.dispatchEvent(event);
                        }
                    }, false);

                    // Asegurar que el clic en el dropZone abra el selector
                    dropZone.addEventListener('click', (e) => {
                        // Evitar bucle si el clic viene del propio input o del label
                        if (e.target !== fileInput && !e.target.closest('label')) {
                            console.log('Click en drop-zone, abriendo selector');
                            fileInput.click();
                        }
                    });
                }
            });
        </script>
    @endpush
</x-layouts.admin>
