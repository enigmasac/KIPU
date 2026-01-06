<?php

namespace Modules\Inventory\Jobs\TransferOrders;

use App\Abstracts\Job;
use Modules\Inventory\Models\TransferOrderItem;
use Modules\Inventory\Models\Item as InventoryItem;
use Modules\Inventory\Jobs\Items\CreateInventoryItem;
use Modules\Inventory\Jobs\TransferOrders\CreateTransferOrderHistory;

class TransferOrderCancelled extends Job
{
    protected $transfer_order;

    /**
     * Create a new job instance.
     *
     * @param  $request
     */
    public function __construct($transfer_order)
    {
        $this->transfer_order = $transfer_order;
    }

    /**
     * Execute the job.
     *
     * @return TransferOrderItem
     */
    public function handle()
    {
        \DB::transaction(function () {
            foreach ($this->transfer_order->transfer_order_items as $transfer_order_item) {
                if ($this->transfer_order->status == 'transferred') {
                    $destination_inventory_item = InventoryItem::where('warehouse_id', $this->transfer_order->destination_warehouse_id)
                                                                ->where('item_id', $transfer_order_item->item_id)
                                                                ->first();

                    $source_inventory_item = InventoryItem::where('warehouse_id', $this->transfer_order->source_warehouse_id)
                                                            ->where('item_id', $transfer_order_item->item_id)
                                                            ->first();

                    $destination_quantity = $destination_inventory_item->opening_stock - $transfer_order_item->transfer_quantity;

                    $destination_inventory_item->update(['opening_stock' => $destination_quantity]);

                    if (empty($source_inventory_item)){
                        $inventory_item_request = [
                            'company_id' => $destination_inventory_item->company_id,
                            'item_id' => $transfer_order_item->item_id,
                            'opening_stock' => $transfer_order_item->transfer_quantity,
                            'opening_stock_value' => $transfer_order_item->transfer_quantity,
                            'warehouse_id' => $this->transfer_order->destination_warehouse_id,
                            'sku' => $destination_inventory_item->sku,
                        ];

                        $source_inventory_item = $this->dispatch(new CreateInventoryItem($inventory_item_request));
                    } else {
                        $destination_inventory_item = InventoryItem::where('warehouse_id', $this->transfer_order->destination_warehouse_id)
                                                                    ->where('item_id', $transfer_order_item->item_id)
                                                                    ->first();

                        $source_quantity = $source_inventory_item->opening_stock + $transfer_order_item->transfer_quantity;

                        $source_inventory_item->update(['opening_stock' => $source_quantity]);
                    }
                }
            }

            $this->transfer_order->update(['status' => 'cancelled']);

            $this->dispatch(new CreateTransferOrderHistory($this->transfer_order));
        });

        return $this->transfer_order;
    }
}
