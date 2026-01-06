<?php

namespace Modules\Inventory\Widgets\Items;

use App\Abstracts\Widget;

class TotalCurrentStock extends Widget
{
    public $default_name = 'inventory::widgets.total_current_stock';

    public $description = 'widgets.description.expenses_by_category';

    public function show($item = null)
    {
        foreach ($item->inventory()->get() as $inventory_item) {
            if (empty($inventory_item->warehouse)) {
                continue;
            }

            $rand = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b'];
            $color = '#' . $rand[rand(0, 11)] . $rand[rand(0, 11)] . $rand[rand(0, 11)] . $rand[rand(0, 11)] . $rand[rand(0, 11)] . $rand[rand(0, 11)];

            $label = $inventory_item->opening_stock . ' - ' . $inventory_item->warehouse->name;

            $this->addToDonut($color, $label, $inventory_item->opening_stock);
        }

        $chart = $this->getDonutChart(trans_choice('general.incomes', 1), '100%', 300, 6);

        return $this->view('inventory::widgets.donut_chart', [
            'chart' => $chart,
        ]);
    }
}
