<x-layouts.modules>
    <x-slot name="title">
        {{ trans_choice('general.modules', 2) }}
    </x-slot>

    <x-slot name="buttons">
    </x-slot>

    <x-slot name="content">
        <x-modules.banners />

        <x-modules.pre-sale />

        <x-modules.paid />

        <x-modules.nnew />

        <x-modules.free />
    </x-slot>

    <x-script folder="modules" file="apps" />
</x-layouts.modules>
