<?php

namespace Modules\Inventory\Jobs\TransferOrders;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use Modules\Inventory\Models\TransferOrder;
use Modules\Inventory\Events\TransferOrderCreated;

class CreateTransferOrder extends Job implements HasOwner, HasSource, ShouldCreate
{
    public function handle(): TransferOrder
    {
        \DB::transaction(function () {
            $this->request->merge(['status' => 'draft']);

            $this->model = TransferOrder::create($this->request->all());

            foreach ($this->request->items as $item) {
                $transfer_order_item_request = [
                    'company_id'        => company_id(),
                    'transfer_order_id' => $this->model->id,
                    'item_id'           => $item['item_id'],
                    'item_quantity'     => $item['item_quantity'],
                    'transfer_quantity' => $item['transfer_quantity'],
                ];

                $this->dispatch(new CreateTransferOrderItem($transfer_order_item_request));
            }

            $this->dispatch(new CreateTransferOrderHistory($this->model));

            event(new TransferOrderCreated($this->model));
        });

        return $this->model;
    }
}
