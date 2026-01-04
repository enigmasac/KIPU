@php
    $search_list_key = array_values(array_unique(array_merge((array) $searchListKey, ['value', 'sku', 'sunat_unit_code'])));
@endphp
<akaunting-item-button
    placeholder="{{ trans('general.placeholder.item_search') }}"
    no-data-text="{{ trans('general.no_data') }}"
    no-matching-data-text="{{ trans('general.no_matching_data') }}"
    type="{{ $type }}"
    price="{{ $price }}"
    :dynamic-currency="currency"
    :items="{{ json_encode($items) }}"
    search-url="{{ $searchUrl }}"
    :search-char-limit="{{ $searchCharLimit }}"
    :search-list-key="{{ json_encode($search_list_key) }}"
    @item="onSelectedItem($event)"
    add-item-text="{{ trans('general.form.add_an', ['field' => trans_choice('general.items', 1)]) }}"
    create-new-item-text="{{ trans('general.title.new', ['type' =>  trans_choice('general.items', 1)]) }}"
></akaunting-item-button>
