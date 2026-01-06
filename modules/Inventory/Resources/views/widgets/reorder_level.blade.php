<div id="widget-{{ $class->model->id }}" class="lg:w-1/2 mt-10 mrl5">
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

    <ul class="text-sm space-y-3 my-3">
        @foreach($items as $item)
            <li class="flex justify-between">
                {{ $item->warehouse_name }}
                <span class="font-medium">{{ $item->reorder_level }}</span>
            </li>
        @endforeach
    </ul>
</div>

