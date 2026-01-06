<x-layouts.admin>
    <x-slot name="title">Configuración SUNAT</x-slot>

    <x-slot name="content">
        <x-form.container>
            <x-form id="setting" method="PATCH" route="sunat.configuration.update">
                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head 
                            title="Configuración General" 
                            description="Configure el ambiente y las opciones de emisión automática a SUNAT" 
                        />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.select 
                            name="environment" 
                            label="Ambiente SUNAT" 
                            :options="['beta' => 'Beta (Pruebas)', 'production' => 'Producción']"
                            :selected="$environment"
                        />

                        <x-form.group.toggle 
                            name="auto_emit" 
                            label="Emisión Automática" 
                            :value="$autoEmit ? 1 : 0"
                        />
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head 
                            title="Credenciales Clave SOL" 
                            description="Ingrese sus credenciales de Clave SOL proporcionadas por SUNAT" 
                        />
                    </x-slot>

                    <x-slot name="body">
                        <x-form.group.text 
                            name="ruc" 
                            label="RUC" 
                            :value="$ruc"
                            placeholder="20000000001"
                        />

                        <x-form.group.text 
                            name="sol_user" 
                            label="Usuario SOL" 
                            :value="$solUser"
                            placeholder="MODDATOS"
                        />

                        <x-form.group.password 
                            name="sol_password" 
                            label="Clave SOL" 
                            placeholder="Dejar en blanco para mantener la actual"
                            not-required
                        />

                        <div class="sm:col-span-6">
                            <div class="bg-gray-200 rounded-md p-3">
                                <small>
                                    <strong>Credenciales de prueba (Beta):</strong><br>
                                    RUC: 20000000001 | Usuario: MODDATOS | Clave: moddatos
                                </small>
                            </div>
                        </div>
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="head">
                        <x-form.section.head 
                            title="Certificado Digital" 
                            description="Gestione el certificado digital para firmar los comprobantes electrónicos" 
                        />
                    </x-slot>

                    <x-slot name="body">
                        @if($certificate)
                            <div class="sm:col-span-6">
                                <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                                    <div class="flex items-start">
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-sm font-medium text-green-800">
                                                Certificado Activo: {{ $certificate->name }}
                                            </h3>
                                            <div class="mt-2 text-sm text-green-700">
                                                <p>Fecha de expiración: {{ $certificate->expires_at?->format('d/m/Y') }}</p>
                                            </div>
                                            <div class="mt-4">
                                                <a href="{{ route('sunat.configuration.certificate') }}" class="text-sm font-medium text-green-800 hover:text-green-700">
                                                    Gestionar certificados →
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="sm:col-span-6">
                                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                    <div class="flex items-start">
                                        <div class="ml-3 flex-1">
                                            <h3 class="text-sm font-medium text-yellow-800">
                                                No hay certificado configurado
                                            </h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>Necesita subir un certificado digital (.pfx o .p12) para poder emitir comprobantes electrónicos.</p>
                                            </div>
                                            <div class="mt-4">
                                                <a href="{{ route('sunat.configuration.certificate') }}" class="inline-flex items-center rounded-md bg-yellow-100 px-3 py-2 text-sm font-semibold text-yellow-800 hover:bg-yellow-200">
                                                    Subir certificado
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </x-slot>
                </x-form.section>

                <x-form.section>
                    <x-slot name="foot">
                        <x-form.buttons :cancel="url()->previous()" />
                    </x-slot>
                </x-form.section>

                <x-form.input.hidden name="_prefix" value="sunat" />
            </x-form>
        </x-form.container>
    </x-slot>

    <x-script folder="settings" file="settings" />
</x-layouts.admin>
