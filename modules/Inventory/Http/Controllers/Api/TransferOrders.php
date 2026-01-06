<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Abstracts\Http\ApiController;
use Modules\Inventory\Models\TransferOrder;
use Modules\Inventory\Jobs\TransferOrders\CreateTransferOrder;
use Modules\Inventory\Jobs\TransferOrders\DeleteTransferOrder;
use Modules\Inventory\Jobs\TransferOrders\UpdateTransferOrder;
use Modules\Inventory\Http\Requests\TransferOrder as Request;
use Modules\Inventory\Http\Resources\TransferOrder as Resource;

class TransferOrders extends ApiController
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Add CRUD permission check
        $this->middleware('permission:create-common-companies')->only('create', 'store');
        $this->middleware('permission:read-common-companies')->only('index', 'show');
        $this->middleware('permission:update-common-companies')->only('enable', 'disable');
        $this->middleware('permission:delete-common-companies')->only('destroy');
        $this->middleware('permission:read-common-companies')->only('edit');
        $this->middleware('permission:update-common-companies')->only('update');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $transfer_orders = TransferOrder::collect();

        return Resource::collection($transfer_orders);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $transfer_order = TransferOrder::find($id);

        if (! $transfer_order instanceof TransferOrder) {
            return $this->errorInternal('No query results for model [' . TransferOrder::class . '] ' . $id);
        }

        return new Resource($transfer_order);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $transfer_order = $this->dispatch(new CreateTransferOrder($request));
            
        return $this->created(route('api.transfer-orders.show', $transfer_order->id), new Resource($transfer_order));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $transfer_order
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(TransferOrder $transfer_order, Request $request)
    {
        $transfer_order = $this->dispatch(new UpdateTransferOrder($transfer_order, $request));

        return new Resource($transfer_order->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  TransferOrder  $transfer_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransferOrder $transfer_order)
    {
        try {
            $this->dispatch(new DeleteTransferOrder($transfer_order));

            return $this->noContent();
        } catch(\Exception $e) {
            $this->errorUnauthorized($e->getMessage());
        }
    }
}
