<?php

namespace Modules\Inventory\Jobs\TransferOrders;

use App\Abstracts\Job;
use Modules\Inventory\Models\Item as InventoryItem;
use Modules\Inventory\Models\TransferOrderItem;
use Modules\Inventory\Jobs\Items\CreateInventoryItem;
use Modules\Inventory\Jobs\Histories\CreateHistory;
use Modules\Inventory\Jobs\TransferOrders\CreateTransferOrderHistory;

class TransferOrderTransferred extends Job
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
                $destination_inventory_item = InventoryItem::where('warehouse_id', $this->transfer_order->destination_warehouse_id)
                                                            ->where('item_id', $transfer_order_item->item_id)
                                                            ->first();

                $source_inventory_item = InventoryItem::where('warehouse_id', $this->transfer_order->source_warehouse_id)
                                                        ->where('item_id', $transfer_order_item->item_id)
                                                        ->first();

                $source_quantity = $source_inventory_item->opening_stock - $transfer_order_item->transfer_quantity;

                $source_inventory_item->update(['opening_stock' => $source_quantity]);

                if (empty($destination_inventory_item)){
                    $inventory_item_request = [
                        'company_id' => $source_inventory_item->company_id,
                        'item_id' => $transfer_order_item->item_id,
                        'opening_stock' => $transfer_order_item->transfer_quantity,
                        'opening_stock_value' => $transfer_order_item->transfer_quantity,
                        'warehouse_id' => $this->transfer_order->destination_warehouse_id,
                        'sku' => $source_inventory_item->sku,
                        'created_from' => $this->transfer_order->created_from,
                        'created_by' => $this->transfer_order->created_by
                    ];

                    $destination_inventory_item = $this->dispatch(new CreateInventoryItem($inventory_item_request));
                } else{
                    $destination_inventory_item = InventoryItem::where('warehouse_id', $this->transfer_order->destination_warehouse_id)
                                                                ->where('item_id', $transfer_order_item->item_id)
                                                                ->first();

                    $destination_quantity = $destination_inventory_item->opening_stock + $transfer_order_item->transfer_quantity;

                    $destination_inventory_item->update(['opening_stock' => $destination_quantity]);
                }
            }

            $this->transfer_order->update(['status' => 'transferred']);

            $this->dispatch(new CreateTransferOrderHistory($this->transfer_order));

            $source_warehouse_history_request = [
                'company_id'    => $this->transfer_order->company_id,
                'user_id'       => user()->id,
                'item_id'       => $transfer_order_item->item_id,
                'type_id'       => $this->transfer_order->id,
                'type_type'     => get_class($this->transfer_order),
                'warehouse_id'  => $this->transfer_order->source_warehouse_id,
                'quantity'      => $transfer_order_item->transfer_quantity,
            ];

            $this->dispatch(new CreateHistory($source_warehouse_history_request));

            $destination_warehouse_history_request = [
                'company_id'    => $this->transfer_order->company_id,
                'user_id'       => user()->id,
                'item_id'       => $transfer_order_item->item_id,
                'type_id'       => $this->transfer_order->id,
                'type_type'     => get_class($this->transfer_order),
                'warehouse_id'  => $this->transfer_order->destination_warehouse_id,
                'quantity'      => $transfer_order_item->transfer_quantity,
            ];

            $this->dispatch(new CreateHistory($destination_warehouse_history_request));
        });

        return $this->transfer_order;
    }
}
