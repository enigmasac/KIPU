<x-layouts.admin>
    @php
        $active_tab = request('tab', 'invoice');
        $tabs = [
            'invoice' => 'Factura',
            'boleta' => 'Boleta',
            'credit-note' => 'Nota de Crédito',
            'debit-note' => 'Nota de Débito',
        ];
    @endphp

    <x-slot name="title">
        {{ trans_choice('general.documents', 2) }}
    </x-slot>

    <x-slot name="content">
        <div class="space-y-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('settings.invoice.edit', ['tab' => $key]) }}" 
                           class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $active_tab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div>
                <x-form.container>
                    @include('settings.invoice.form_content', ['prefix' => $active_tab, 'title' => $tabs[$active_tab]])
                </x-form.container>
            </div>
        </div>
    </x-slot>

    <x-script folder="settings" file="settings" />
</x-layouts.admin>
