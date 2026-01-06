<?php

namespace Modules\Inventory\Widgets\Items;

use App\Abstracts\Widget;

class WarehouseReorderLevel extends Widget
{
    public $default_name = 'inventory::widgets.reorder_level';

    public function show($item = null)
    {
        foreach ($item->inventory()->get() as $inventory_item) {
            if (empty($inventory_item->warehouse)) {
                continue;
            }

            $warehouse[] = (object) [
                'reorder_level' => $inventory_item->reorder_level,
                'warehouse_name' => $inventory_item->warehouse->name];
                
            arsort($warehouse);

            if (empty($warehouse)) {
                return false;
            }
        }

        if (empty($warehouse)) {
            return false;
        }

        if (count($warehouse) == 0) {
            $warehouse = [];
        } elseif (count($warehouse) > 5) {
            return $this->view('inventory::widgets.reorder_level', [
                'items' => array_slice($warehouse, 0, 5),
            ]);
        }

        return $this->view('inventory::widgets.reorder_level', [
            'items' => $warehouse,
        ]);
    }
}
