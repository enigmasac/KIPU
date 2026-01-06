@php
    $rows = $class->row_values[$table_key][$id];
    $row_total = array_sum($rows);
@endphp

<li>
    <div class="border-0 m-0 p-0">
        <div class="flex justify-between">
            <div class="flex items-center">
                <span>{{ $class->row_names[$table_key][$id] }}</span>
            </div>
            <span>{{ $row_total }}</span>
        </div>
    </div>
</li>
