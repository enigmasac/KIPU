<?php

namespace Modules\Inventory\Listeners;

use App\Traits\Modules;
use App\Models\Common\Item;
use App\Events\Report\RowsShowing;
use App\Events\Report\GroupShowing;
use App\Events\Report\GroupApplying;
use App\Abstracts\Listeners\Report as Listener;

class AddInventoryToReports extends Listener
{
    use Modules;

    protected $classes = [
        'App\Reports\IncomeSummary',
        'App\Reports\ExpenseSummary',
        'App\Reports\IncomeExpenseSummary',
        'App\Reports\ProfitLoss'
    ];
    
    /**
     * Handle group showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleGroupShowing(GroupShowing $event)
    {
        if (! $this->moduleIsEnabled('inventory') || 
            $this->skipThisClass($event) ||
            $this->skipRowsShowing($event, 'item')
            ) {
            return;
        }

        $event->class->groups['item'] = trans_choice('inventory::general.name', 1);
    }

        /**
     * Handle group applying event.
     *
     * @param  $event
     * @return void
     */
    public function handleGroupApplying(GroupApplying $event)
    {
        if (! $this->moduleIsEnabled('inventory') || 
            $this->skipThisClass($event) ||
            $this->skipRowsShowing($event, 'item')
            ) {
            return;
        }

        $event->model->item_id = 0;

        if (!in_array($event->model->getTable(), ['documents'])) {
            return;
        }

        $event->model->item_id = $event->model->items()->pluck('item_id')->first();
    }

    /**
     * Handle rows showing event.
     *
     * @param  $event
     * @return void
     */
    public function handleRowsShowing(RowsShowing $event)
    {
        if (! $this->moduleIsEnabled('inventory') || 
            $this->skipThisClass($event) ||
            $this->skipRowsShowing($event, 'item')
            ) {
            return;
        }

        $items = $this->getItems();

        $item_list = $items->pluck('name', 'id')->toArray();

        asort($item_list);

        if ($items = request('items')) {
            $rows = collect($item_list)->filter(function ($value, $key) use ($items) {
                return in_array($key, $items);
            });
        } else {
            $rows = $item_list;
        }

        $this->setRowNamesAndValues($event, $rows);
    }

    public function getItems()
    {
        $items = Item::orderBy('name');

        $request = request()->all();

        if ($request) {
            $search = str_replace('"', '', request('search'));

            if ($search) {
                $items->where('name', 'like', '%' . $search . '%');
            }
        }

        $items->get();

        foreach ($items as $key => $item) {
            if (! $item->inventory()->first()) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}
