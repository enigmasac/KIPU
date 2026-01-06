<?php

namespace Modules\Inventory\Http\ViewComposers;

use App\Traits\Modules;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Common\Item as CoreItem;
use Modules\Inventory\Models\DocumentItem as InventoryDocumentItem;

class DocumentItem
{
    use Modules;

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (! $this->moduleIsEnabled('inventory')) {
            return;
        }

        $request = request();

        $inventory_type = config('type.document.' . Str::singular((string) $request->segment(3)) . '.inventory_stock_action');

        if (! $inventory_type) {
            return;
        }

        $warehouses = $item_warehouses = [];

        $document_type_class = 'inventory::classes.income';
        $document_type_name = 'general.sales';

        if ($request->segment(1) == 'purchases') {
            $document_type_class = 'inventory::classes.expenses';
            $document_type_name = 'general.purchases';
        }

        foreach (json_decode(setting('inventory.units')) as $key => $value) {
            $units[$value] = $value;
        }

        $item_selected_warehouse = [];

        if ($request->routeIs('invoices.edit') || $request->routeIs('bills.edit')) {
            $document = $request->route(Str::singular((string) $request->segment(3)));

            if ($document && isset($document->items)) {
                foreach ($document->items as $key => $item) {
                    $warehouse_id = null;

                    if ($request->routeIs('invoices.edit')) {
                        $warehouse_id = InventoryDocumentItem::where('document_id', $item->document_id)->where('document_item_id', $item->id)->value('warehouse_id');
                    }

                    if (!empty($warehouse_id)) {
                        $item_selected_warehouse[$item->item_id][$key] = $warehouse_id;
                    }
                }
            }
        }

        $warehouses = Warehouse::enabled()->pluck('name', 'id');

        $input_warehouse_col = 'col-md-12 mb-0';

        $item_warehouses = [];
        $item_default_warehouse = [];
        $input_model = '';

        if (setting('inventory.track_inventory') == true) {
            $item_default_warehouse[0] = setting('inventory.default_warehouse');
        }

        $selected = null;

        $items = CoreItem::with('inventories')->enabled()->get();
        $selected = null;

        foreach ($items as $item) {
            $inventory_items = $item->inventories;

            if (! $inventory_items->count()) {
                continue;
            }

            $inventory_item_warehouses = [];
            $inventory_item_default = '';

            foreach ($inventory_items as $inventory_item) {
                if (!empty($inventory_item->warehouse)) {
                    if ($inventory_item->warehouse->enabled == false) {
                        continue;
                    }

                    $inventory_item_warehouses[$inventory_item->warehouse->id] = $inventory_item->warehouse->name;
                    $inventory_item_warehouses[$inventory_item->warehouse->id] .= ' (' . trans('inventory::general.stock') . ': ' . $inventory_item->opening_stock . ')';
                
                    if ($inventory_item->default_warehouse) {
                        $inventory_item_default = $inventory_item->warehouse_id;
                    }
                }
            }

            if (!empty($inventory_item_warehouses)) {
                $item_warehouses[$item->id] = !empty($inventory_item_warehouses) ? $inventory_item_warehouses : [];
                $item_default_warehouse[$inventory_item->item_id] = !empty($inventory_item_default) ? $inventory_item_default : '';

                if ($request->routeIs('invoices.create') || $request->routeIs('bills.create')) {
                    $selected = !empty($inventory_item_default) ? $inventory_item_default : setting('inventory.default_warehouse');
                    $input_model = 'this.item_default_warehouse[row.item_id].toString()';
                } else {
                    $input_model = '(this.item_selected_warehouse[row.item_id] == undefined || this.item_selected_warehouse[row.item_id][index] == undefined) ? this.item_default_warehouse[row.item_id].toString() : this.item_selected_warehouse[row.item_id][index].toString()';
                }
            }
        }
        
        // Push to a stack
        $view->getFactory()->startPush('item_custom_fields', view('inventory::partials.input_document_item', compact('selected', 'input_model', 'warehouses', 'document_type_name', 'units')));

        $view->getFactory()->startPush('scripts', view('inventory::partials.script', compact('input_model', 'item_warehouses', 'item_default_warehouse', 'item_selected_warehouse')));
    }
}