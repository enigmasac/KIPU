<?php

namespace Modules\Inventory\Reports;

use App\Abstracts\Report;
use App\Utilities\Date;
use App\Models\Common\Item;
use App\Models\Document\DocumentItem;
use Modules\Inventory\Models\History;

class PurchaseItem extends Report
{
    public $default_name = 'inventory::general.reports.name.purchase_summary';

    public $category = 'inventory::general.name';

    public $icon = 'inventory_2';

    public $type = 'summary';

    public $bar_formatter_type = 'integer';

    public $chart = [
        'bar' => [
            'colors' => [
                '#fb7185',
            ],
        ],
        'donut' => [
            //
        ],
    ];

    public function setViews()
    {
        parent::setViews();
        $this->views['detail.table.footer'] = 'inventory::partials.reports.detail.table.footer';
        $this->views['detail.table.row'] = 'inventory::partials.reports.detail.table.row';
        $this->views['summary.table.body'] = 'inventory::partials.reports.summary.table.body';
        $this->views['summary.table.row'] = 'inventory::partials.reports.summary.table.row';
    }
    
    public function setTables()
    {
        $this->tables = [
            'purchase' => trans_choice('general.purchases', 2),
        ];
    }

    public function setRows()
    {
        $rows = [];

        $items = $this->getItems();

        if (! $items->count()) {
            return;
        }

        foreach ($items as $item) {
            if (! $item->inventory()->first()) {
                continue;
            }

            $rows += [$item->name => $item->name];
        }

        $this->setRowNamesAndValues($rows);
    }

    public function setRowNamesAndValues($rows)
    {
        $nodes = [];

        foreach ($this->dates as $date) {
            foreach ($this->tables as $table_key => $table_name) {
                foreach ($rows as $id => $name) {
                    $this->row_names[$table_key][$id] = $name;
                    $this->row_values[$table_key][$id][$date] = 0;

                    $nodes[$id] = null;
                }
            }
        }

        $this->setTreeNodes($nodes);
    }

    public function setTreeNodes($nodes)
    {
        foreach ($this->tables as $table_key => $table_name) {
            foreach ($nodes as $id => $node) {
                $this->row_tree_nodes[$table_key][$id] = $node;
            }
        }
    }

    public function setData()
    {
        foreach (Item::get() as $item) {
            if (! $item->inventory()->first()) {
                continue;
            }

            $histories = $this->applyFilters(History::document()->where('item_id', $item->id), ['date_field' => 'created_at'])->get();

            foreach ($histories as $history) {
                $document_item = DocumentItem::bill()->find($history->type_id);

                if (! isset($document_item->document)) {
                    continue;
                }

                $date = $this->getFormattedDate(Date::parse($history->created_at));

                if (!empty($document_item) && isset($this->row_values['purchase'][$item->name][$date])) {
                    $this->row_values['purchase'][$item->name][$date] += $history->quantity;
                    $this->footer_totals['purchase'][$date] += $history->quantity;
                }
            }
        }
    }
    
    public function getItems()
    {
        $model = Item::orderBy('name');

        $request = request()->all();

        if ($request) {
            $search = str_replace('"', '', request('search'));

            if ($search) {
                $model->where('name', 'like', '%' . $search . '%');
            }
        }

        return $model->get();
    }
}
