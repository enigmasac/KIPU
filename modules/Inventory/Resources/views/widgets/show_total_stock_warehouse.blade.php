<ul id="widget-{{ $class->model->id }}" class="lg:w-1/3 mt-10 mr-5" role="list">
    <li class="col-span-1 flex shadow-sm rounded-md">
        <div class="flex-shrink-0 flex items-center justify-center w-16 bg-status-success text-white text-sm font-medium rounded-l-md">
            <span class="material-icons-outlined">
                category
            </span>
        </div>
        <div class="flex-1 flex items-center justify-between border-t border-r border-b border-gray-200 bg-white rounded-r-md truncate">
            <div class="flex-1 px-4 py-2 text-sm truncate">
                <p class="text-gray-900 font-medium hover:text-gray-600">
                    {{ trans('inventory::widgets.total_stock') }}
                </p>

                <p class="text-gray-500">
                    {{ $stock }}
                </p>
            </div>
        </div>
    </li>
</ul>
