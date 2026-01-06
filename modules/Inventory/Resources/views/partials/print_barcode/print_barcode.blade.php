<x-layouts.print>
    <x-slot name="title">
        
    </x-slot>
    <x-slot name="content"> 
        @if(! empty(setting('inventory.barcode_print_template')))
            @include('inventory::partials.print_barcode.templates.' . setting('inventory.barcode_print_template'))
        @else
            @include('inventory::partials.print_barcode.templates.single')
        @endif
    </x-slot>
</x-layout-print>