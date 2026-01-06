<x-layouts.admin>
    <x-slot name="title">Emisiones SUNAT</x-slot>

    <x-slot name="content">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Historial de Emisiones SUNAT</h3>
            </div>
            <div class="card-body">
                @if($emissions->isEmpty())
                    <div class="alert alert-info">No hay emisiones registradas.</div>
                @else
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Código SUNAT</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emissions as $emission)
                                <tr>
                                    <td>{{ $emission->document_number }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $emission->document_type)) }}</td>
                                    <td>
                                        @switch($emission->status)
                                            @case('accepted') <span class="badge badge-success">Aceptado</span> @break
                                            @case('rejected') <span class="badge badge-danger">Rechazado</span> @break
                                            @case('pending') <span class="badge badge-warning">Pendiente</span> @break
                                            @case('error') <span class="badge badge-secondary">Error</span> @break
                                            @default <span class="badge badge-info">{{ $emission->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $emission->sunat_code }}</td>
                                    <td>{{ Str::limit($emission->sunat_message, 50) }}</td>
                                    <td>{{ $emission->emitted_at?->format('d/m/Y H:i') ?? $emission->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $emissions->links() }}
                @endif
            </div>
        </div>
    </x-slot>
</x-layouts.admin>
