<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Abstracts\Http\ApiController;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Jobs\Warehouses\CreateWarehouse;
use Modules\Inventory\Jobs\Warehouses\DeleteWarehouse;
use Modules\Inventory\Jobs\Warehouses\UpdateWarehouse;
use Modules\Inventory\Http\Requests\Warehouse as Request;
use Modules\Inventory\Http\Resources\Warehouse as Resource;

class Warehouses extends ApiController
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
        $warehouses = Warehouse::collect();

        return Resource::collection($warehouses);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $warehouse = Warehouse::find($id);

        if (! $warehouse instanceof Warehouse) {
            return $this->errorInternal('No query results for model [' . Warehouse::class . '] ' . $id);
        }

        return new Resource($warehouse);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $warehouse = $this->dispatch(new CreateWarehouse($request));
            
        return $this->created(route('api.warehouses.show', $warehouse->id), new Resource($warehouse));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $warehouse
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Warehouse $warehouse, Request $request)
    {
        $warehouse = $this->dispatch(new UpdateWarehouse($warehouse, $request));

        return new Resource($warehouse->fresh());
    }

    /**
     * Enable the specified resource in storage.
     *
     * @param  Warehouse  $warehouse
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Warehouse $warehouse)
    {
        $warehouse = $this->dispatch(new UpdateWarehouse($warehouse, request()->merge(['enabled' => 1])));

        return new Resource($warehouse->fresh());
    }

    /**
     * Disable the specified resource in storage.
     *
     * @param  Warehouse  $warehouse
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(Warehouse $warehouse)
    {
        $warehouse = $this->dispatch(new UpdateWarehouse($warehouse, request()->merge(['enabled' => 0])));

        return new Resource($warehouse->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Warehouse  $warehouse
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            $this->dispatch(new DeleteWarehouse($warehouse));

            return $this->noContent();
        } catch(\Exception $e) {
            $this->errorUnauthorized($e->getMessage());
        }
    }
}
