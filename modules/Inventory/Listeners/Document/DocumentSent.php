<?php

namespace Modules\Inventory\Listeners\Document;

use App\Events\Document\DocumentSent as Event;
use App\Traits\Modules;
use App\Traits\Jobs;
use Modules\Inventory\Jobs\Histories\CreateHistory;
use Modules\Inventory\Models\History as InventoryHistory;
use Modules\Inventory\Models\DocumentItem as InventoryDocumentItem;
use Modules\Inventory\Models\Item as InventoryItem;

class DocumentSent
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
        if (! $this->moduleIsEnabled('inventory')) {
            return;
        }
        
        // To do: The document senting event should be listened to.
        $sent_count = $event->document->histories->where('status', 'sent')->count();

        if ($sent_count > 1) {
            return;
        }

        $return_statuses = ['cancelled', 'partial', 'viewed', 'paid'];

        $inventory_stock_action = config('type.document.' . $event->document->type . '.inventory_stock_action');

        if (in_array($event->document->status, $return_statuses) || empty($inventory_stock_action)) {
            return;
        }

        $user = user();
        $user_id = !empty($user) ? $user->id : 0;

        foreach ($event->document->items as $item) {
            $warehouse_id = InventoryDocumentItem::where('document_id', $event->document->id)->where('document_item_id', $item->id)->value('warehouse_id');

            $inventory_item = InventoryItem::where('warehouse_id', $warehouse_id)->where('item_id', $item->item_id)->first();

            if (empty($inventory_item)) {
                continue;
            }

            if ($inventory_stock_action == 'decrease') {
                $inventory_item->opening_stock -= (float) $item->quantity;
            } else {
                $inventory_item->opening_stock += (float) $item->quantity;
            }

            $inventory_item->save();

            InventoryHistory::where('type_type', get_class($item))
                ->where('type_id', $item->id)
                ->where('warehouse_id', $warehouse_id)
                ->where('item_id', $inventory_item->item->id)
                ->delete();

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
