<div id="widget-{{ $class->model->id }}" class="w-full my-8 px-12">
    @include($class->views['header'], ['header_class' => ''])
    <div class="flex flex-col lg:flex-row mt-3">
        <div class="w-full lg:w-11/12">
            {!! $chart->container() !!}
        </div>

        <div class="w-full lg:w-1/12 mt-11 space-y-2">
            <div class="flex flex-col items-center justify-between text-center">
                <div class="flex justify-end lg:block text-lg">
                    <x-money :amount="$totals['incoming']" :currency="setting('default.currency')" convert />
                </div>

                <span class="text-green text-xs">{{ trans('general.incoming') }}</span>
            </div>

            <div class="flex flex-col items-center justify-between">
                <div class="flex justify-end lg:block text-lg">
                    <x-money :amount="$totals['outgoing']" :currency="setting('default.currency')" convert />
                </div>

                <span class="text-rose text-xs">{{ trans('general.outgoing') }}</span>
            </div>
        </div>
    </div>
</div>

@push('body_scripts')
    {!! $chart->script() !!}
@endpush
