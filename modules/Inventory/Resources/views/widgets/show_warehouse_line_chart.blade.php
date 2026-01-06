<div id="widget-{{ $class->model->id }}" class="lg:w-1/1 mt-10">
    <div class="pb-2 my-4 lg:my-0">
        <div class="flex justify-between font-medium mb-2">
            <h2 class="text-black" title="{{ $class->model->name }}">
                {{ $class->model->name }}
            </h2>
        </div>
        <span class="h-6 block border-b text-black-400 text-xs truncate">
            {{ $class->getDescription() }}
        </span>
    </div>

    <div class="flex flex-col-reverse lg:flex-row mt-3">
        <div class="w-full lg:w-11/12 apex-chart-cash-flow">
            {!! $chart->container() !!}
        </div>

        <div class="w-full lg:w-1/12 flex flex-row lg:flex-col items-center justify-around sm:justify-start lg:mt-11 space-y-0 sm:space-y-2">
            <div class="relative w-32 lg:w-auto flex flex-col items-center sm:justify-between text-center">
                <span class="text-green cash-flow-text">{{ trans('inventory::widgets.total_stock') }}</span>

                <div class="flex justify-end lg:block text-lg">
                    {{ $totals['item'] }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('body_scripts')
    {!! $chart->script() !!}
@endpush
