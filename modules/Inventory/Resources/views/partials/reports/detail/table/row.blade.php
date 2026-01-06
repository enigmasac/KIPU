@if (is_null($node))
    @php $rows = $class->row_values[$table_key][$id]; @endphp
    @if ($row_total = array_sum($rows))
        @if (isset($parent_id))
            <tr class="collapse" id="collapse-{{ $parent_id }}" data-parent="#collapse-{{ $parent_id }}">
        @else
            <tr>
        @endif

        <td class="{{ $class->column_name_width }} py-2 text-left text-black-400" title="{{ $class->row_names[$table_key][$id] }}">{{ $class->row_names[$table_key][$id] }}</td>
        @foreach($rows as $row)
            <td class="{{ $class->column_value_width }} py-2 text-right text-black-400 text-xs">{{ $row }}</td>
        @endforeach
        <td class="{{ $class->column_name_width }} py-2 text-right text-black-400 text-xs uppercase">{{ $row }}</td>
    </tr>
    @endif
@endif
