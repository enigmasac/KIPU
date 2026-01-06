<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Abstracts\Http\ApiController;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Jobs\Variants\CreateVariant;
use Modules\Inventory\Jobs\Variants\DeleteVariant;
use Modules\Inventory\Jobs\Variants\UpdateVariant;
use Modules\Inventory\Http\Requests\Variant as Request;
use Modules\Inventory\Http\Resources\Variant as Resource;

class Variants extends ApiController
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
        $variants = Variant::collect();

        return Resource::collection($variants);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $variants = Variant::find($id);

        if (! $variants instanceof Variant) {
            return $this->errorInternal('No query results for model [' . Variant::class . '] ' . $id);
        }

        return new Resource($variants);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $variant = $this->dispatch(new CreateVariant($request));
            
        return $this->created(route('api.variants.show', $variant->id), new Resource($variant));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $item
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Variant $variant, Request $request)
    {
        $variant = $this->dispatch(new UpdateVariant($variant, $request));

        return new Resource($variant->fresh());
    }

    /**
     * Enable the specified resource in storage.
     *
     * @param  Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Variant $variant)
    {
        $variant = $this->dispatch(new UpdateVariant($variant, request()->merge(['enabled' => 1])));

        return new Resource($variant->fresh());
    }

    /**
     * Disable the specified resource in storage.
     *
     * @param  Variant  $variant
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(Variant $variant)
    {
        $variant = $this->dispatch(new UpdateVariant($variant, request()->merge(['enabled' => 0])));

        return new Resource($variant->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Variant $variant)
    {
        try {
            $this->dispatch(new DeleteVariant($variant));

            return $this->noContent();
        } catch(\Exception $e) {
            $this->errorUnauthorized($e->getMessage());
        }
    }
}
