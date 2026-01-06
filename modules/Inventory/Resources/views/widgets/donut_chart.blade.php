<div id="widget-{{ $class->model->id }}" class="lg:w-1/2 mt-10 mr-5">
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

    <div class="flex flex-col lg:flex-row mt-3" id="widget-donut-{{ $class->model->id }}">
        <div class="w-full">
            {!! $chart->container() !!}
        </div>
    </div>
</div>

@push('body_scripts')
    {!! $chart->script() !!}
@endpush
