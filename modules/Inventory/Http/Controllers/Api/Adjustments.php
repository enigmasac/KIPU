<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Abstracts\Http\ApiController;
use Modules\Inventory\Models\Adjustment;
use Modules\Inventory\Jobs\Adjustments\CreateAdjustment;
use Modules\Inventory\Jobs\Adjustments\DeleteAdjustment;
use Modules\Inventory\Jobs\Adjustments\UpdateAdjustment;
use Modules\Inventory\Http\Requests\Adjustment as Request;
use Modules\Inventory\Http\Resources\Adjustment as Resource;

class Adjustments extends ApiController
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
        $adjustments = Adjustment::collect();

        return Resource::collection($adjustments);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $adjustment = Adjustment::find($id);

        if (! $adjustment instanceof Adjustment) {
            return $this->errorInternal('No query results for model [' . Adjustment::class . '] ' . $id);
        }

        return new Resource($adjustment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $adjustment = $this->dispatch(new CreateAdjustment($request));
            
        return $this->created(route('api.adjustments.show', $adjustment->id), new Resource($adjustment));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $adjustment
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Adjustment $adjustment, Request $request)
    {
        $adjustment = $this->dispatch(new UpdateAdjustment($adjustment, $request));

        return new Resource($adjustment->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Adjustment  $adjustment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Adjustment $adjustment)
    {
        try {
            $this->dispatch(new DeleteAdjustment($adjustment));

            return $this->noContent();
        } catch(\Exception $e) {
            $this->errorUnauthorized($e->getMessage());
        }
    }
}
