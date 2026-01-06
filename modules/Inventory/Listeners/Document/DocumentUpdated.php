<?php

namespace Modules\Inventory\Listeners\Document;

use App\Events\Document\DocumentUpdated as Event;
use App\Models\Document\DocumentItem;
use App\Traits\Modules;
use App\Traits\Jobs;
use Modules\Inventory\Jobs\Histories\CreateHistory;
use Modules\Inventory\Models\Item as InventoryItem;
use Modules\Inventory\Models\DocumentItem as InventoryDocumentItem;
use Modules\Inventory\Models\History as InventoryHistory;

class DocumentUpdated
{
    use Modules, Jobs;

    /**
     * Handle the event.
     *
     * @param  Event $event
     * @return void
     */
    public function handle(Event $event)
    {
        //process being monitored
        return;
        if (! $this->moduleIsEnabled('inventory')) {
            return;
        }

        $return_statuses = ['cancelled', 'draft', 'partial', 'viewed'];

        $inventory_stock_action = config('type.document.' . $event->document->type . '.inventory_stock_action');

        if (in_array($event->document->status, $return_statuses) || empty($inventory_stock_action)) {
            return;
        }

        $user = user();
        $user_id = !empty($user) ? $user->id : 0;

        $items = $event->document->items;

        foreach ($items as $item) {
            DocumentItem::where('document_id', $event->document->id)->where('item_id', $item->item_id)->value('id');

            $warehouse_id = InventoryDocumentItem::where('document_id', $event->document->id)->where('document_item_id', $item->id)->value('warehouse_id');
            $inventory_item = InventoryItem::where('item_id', $item->item_id)->where('warehouse_id', $warehouse_id)->first();

            if (empty($inventory_item)) {
                continue;
            }

            if ($inventory_stock_action == 'decrease') {
                $inventory_item->opening_stock -= (float) $item->quantity;
            } else {
                $inventory_item->opening_stock += (float) $item->quantity;
            }

            $inventory_item->save();

            $history_data = [
                'company_id'    => $item->company_id,
                'user_id'       => $user_id,
                'item_id'       => $item->item->id,
                'type_id'       => $item->id,
                'type_type'     => get_class($item),
                'warehouse_id'  => !empty($warehouse_id) ? $warehouse_id : setting('inventory.default.warehouse'),
                'quantity'      => $item->quantity,
            ];

            $this->ajaxDispatch(new CreateHistory($history_data));
        }
    }
}
