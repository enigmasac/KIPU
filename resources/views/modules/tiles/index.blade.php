<x-layouts.modules>
    <x-slot name="title">
        {{ trans_choice('general.modules', 2) }}
    </x-slot>

    <x-slot name="buttons">
    </x-slot>

    <x-slot name="content">
        <x-modules.items title="{{ $title }}" :model="$modules" see-more />
    </x-slot>

    <x-script folder="modules" file="apps" />
</x-layouts.modules>
