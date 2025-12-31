<x-layouts.modules>
    <x-slot name="title">
        {{ trans('modules.documentation') }}
    </x-slot>

    <x-slot name="buttons">
        <x-link href="{{ route('apps.home.index') }}" class="btn-white">
            {{ trans('general.back') }}
        </x-link>
    </x-slot>

    <x-slot name="content">
        <div>
            <div class="app-documentation flex flex-col gap-4 py-4">
                @if ($documentation)
                    {!! $documentation->body !!}
                @else
                    <x-empty-data />
                @endif
            </div>

            <div class="flex flex-end">
                <x-link href="{{ url($back) }}" class="px-6 py-1.5 hover:bg-gray-200 rounded-lg bg-light-gray">
                    {{ trans('modules.back') }}
                </x-link>
            </div>
        </div>
    </x-slot>

    <x-script folder="modules" file="apps" />
</x-layouts.modules>
