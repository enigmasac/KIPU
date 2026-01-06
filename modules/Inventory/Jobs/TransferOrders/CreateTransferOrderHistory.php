<?php

namespace Modules\Inventory\Jobs\TransferOrders;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use Modules\Inventory\Models\TransferOrderHistory;

class CreateTransferOrderHistory extends Job implements HasOwner, HasSource, ShouldCreate
{
    protected $request;

    /**
     * Create a new job instance.
     *
     * @param  $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function handle(): TransferOrderHistory
    {
        $this->model = TransferOrderHistory::create([
            'company_id'        => company_id(),
            'created_by'        => $this->request->created_by ?? 0,
            'transfer_order_id' => $this->request->id,
            'status'            => $this->request->status
        ]);

        return $this->model;
    }
}
