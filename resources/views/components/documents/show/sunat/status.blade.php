@props(['document'])

@php
    $done = false;
    $open = false; // Default closed unless logic dictates otherwise
    $accordionActive = false; // Or pass logic to determine if it should be active

    if ($document->sunat_status == 'accepted') {
        $done = true;
    }

    // Icon logic based on status
    $icon = match ($document->sunat_status) {
        'accepted' => 'check_circle',
        'rechazado', 'error' => 'error',
        'pendiente' => 'schedule',
        default => 'cloud_off' // Or some neutral icon
    };

    $statusColor = match ($document->sunat_status) {
        'accepted' => 'text-green-600',
        'rechazado', 'error' => 'text-red-600',
        default => 'text-gray-500'
    };

    $title = 'Emitir a SUNAT';
    $description = 'Estado de la emisión electrónica';

    if ($document->sunat_status) {
        $title = match ($document->sunat_status) {
            'accepted' => 'Enviado a SUNAT',
            'rechazado' => 'Rechazado por SUNAT',
            'error' => 'Error de Emisión',
            'pendiente' => 'Pendiente de Emisión',
            default => 'Estado SUNAT'
        };

        $description = $document->sunat_message ?? 'Sin mensajes de respuesta.';
    }

@endphp

<x-show.accordion type="sunat" :open="$open">
    <x-slot name="head">
        <div class="flex items-start">
            <div class="flex-shrink-0 mr-3">
                <span class="material-icons-outlined {{ $statusColor }}">{{ $icon }}</span>
            </div>
            <x-show.accordion.head :title="$title" :description="$description" override="class" class="flex-1" />
        </div>
    </x-slot>

    <x-slot name="body">
        @if($document->sunat_code)
            <div class="mt-2 text-sm text-gray-500">
                <strong>Código:</strong> {{ $document->sunat_code }}
            </div>
        @endif

        @if($document->sunat_status == 'accepted')
            <div class="mt-4 flex gap-2">
                @if($document->latest_sunat_emission && $document->latest_sunat_emission->xml_path)
                    <a href="{{ route('sunat.emissions.xml', $document->latest_sunat_emission->id) }}"
                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="material-icons-outlined text-sm mr-1">description</span> XML
                    </a>
                @endif
                @if($document->latest_sunat_emission && $document->latest_sunat_emission->cdr_path)
                    <a href="{{ route('sunat.emissions.cdr', $document->latest_sunat_emission->id) }}"
                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <span class="material-icons-outlined text-sm mr-1">verified</span> CDR (Constancia)
                    </a>
                @endif
            </div>
        @endif


    </x-slot>
</x-show.accordion>