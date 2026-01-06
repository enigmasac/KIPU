<?php

namespace Modules\Inventory\Models\Common;

use App\Models\Common\Item as CoreItem;
use Modules\Inventory\Models\Item as InventoryItem;

class Item extends CoreItem
{
    public function inventory()
    {
        return $this->belongsTo('Modules\Inventory\Models\Item', 'id', 'item_id');
    }

    public function inventories()
    {
        return $this->hasMany('Modules\Inventory\Models\Item')->with('warehouse', 'item');
    }

    /**
     * Scope to get all rows filtered, sorted and paginated.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $sort
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCollect($query, $sort = 'name')
    {
        $request = request();

        /**
         * Modules that use the sort parameter in CRUD operations cause an error,
         * so this sort parameter set back to old value after the query is executed.
         * 
         * for Custom Fields module
         */
        $request_sort = $request->get('sort');

        $searched_items = $query;

        $query->usingSearchString()->sortable($sort);
        
        $item_ids = $searched_items->pluck('id')->toArray();

        $include_item_ids = [];

        if (! is_null($search = $request->get('search'))) {
            $search = str_replace('"', '', $search);

            if (strpos($request->get('search'), 'warehouse_id') !== false) {
                $search = str_replace('inventory.', '', $search);
                $search = str_replace('warehouse_id:', '', $search);

                if (strpos($request->get('search'), 'not') !== false) {
                    $search = str_replace('not ', '', $search);

                    $include_item_ids = InventoryItem::whereNot('warehouse_id', $search);
                } else {
                    $include_item_ids = InventoryItem::where('warehouse_id', $search);
                }
            } else {
                $include_item_ids = InventoryItem::where('opening_stock', 'like', '%' . $search. '%')
                    ->orWhere('opening_stock_value', 'like', '%' . $search. '%')
                    ->orWhere('reorder_level', 'like', '%' . $search. '%')
                    ->orWhere('barcode', 'like', '%' . $search. '%')
                    ->orWhere('sku', 'like', '%' . $search. '%')
                    ->orWhere('unit', 'like', '%' . $search. '%');
            }
            
            $include_item_ids = $include_item_ids->pluck('item_id')->toArray();
        }

        if ($include_item_ids) {
            $search_warehouse = strpos($request->get('search'), 'warehouse_id');

            $item_ids = isset($search_warehouse) ? $include_item_ids : array_merge($include_item_ids, $item_ids);

            $query = $this->whereIn('id', $item_ids);
        }

        if ($request->expectsJson() && $request->isNotApi()) {
            return $query->get();
        }

        $request->merge(['sort' => $request_sort]);
        // This line disabled because broken sortable issue.
        //$request->offsetUnset('direction');
        $limit = (int) $request->get('limit', setting('default.list_limit', '25'));

        return $query->paginate($limit);
    }

}