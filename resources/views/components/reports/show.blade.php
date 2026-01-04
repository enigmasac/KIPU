<x-layouts.admin>
    <x-slot name="title">
        {{ $class->model->name }}
    </x-slot>

    @php
        $is_system = ! empty($class->model->system_slug);
        $favorite_route = $is_system
            ? ['reports.system.show', $class->model->system_slug]
            : ['reports.show', $class->model->id];
    @endphp

    <x-slot name="favorite"
        title="{{ $class->model->name }}"
        icon="{{ $class->icon }}"
        :route="$favorite_route"
    ></x-slot>

    <x-slot name="buttons">
        @stack('button_print_start')

        <x-link href="{{ url($class->getUrl('print')) }}" target="_blank">
            {{ trans('general.print') }}
        </x-link>

        @stack('button_print_end')

        @stack('button_export_start')

        <x-link href="{{ url($class->getUrl('export')) }}">
            {{ trans('general.export') }}
        </x-link>

        @stack('button_export_end')
    </x-slot>

    <x-slot name="moreButtons">
        <x-dropdown id="dropdown-more-actions">
            <x-slot name="trigger">
                <span class="material-icons pointer-events-none">more_horiz</span>
            </x-slot>

            @stack('button_pdf_start')

            <x-dropdown.link href="{{ url($class->getUrl('pdf')) }}" id="show-more-actions-pdf-report">
                {{ trans('general.download_pdf') }}
            </x-dropdown.link>

            @stack('button_pdf_end')

            <x-dropdown.divider />

            @stack('button_edit_start')

            @can('update-common-reports')
                @unless ($is_system)
                    <x-dropdown.link href="{{ url($class->getUrl('edit')) }}" id="index-more-actions-edit-report">
                        {{ trans('general.edit') }}
                    </x-dropdown.link>
                @endunless
            @endcan

            @stack('button_edit_end')

            @stack('button_delete_start')

            @can('delete-common-reports')
                @unless ($is_system)
                    <x-delete-link :model="$class->model" route="reports.destroy" />
                @endunless
            @endcan

            @stack('button_delete_end')
        </x-dropdown>
    </x-slot>

    <x-slot name="content">
        <div class="my-10">
            <x-loading.content />

            @include($class->views['filter'])

            @include($class->views[$class->type])
        </div>
    </x-slot>

    <x-script folder="common" file="reports" />
</x-layouts.admin>
