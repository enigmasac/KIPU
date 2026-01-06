<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Abstracts\Http\ApiController;
use Modules\Inventory\Models\ItemGroup;
use Modules\Inventory\Jobs\ItemGroups\CreateItemGroup;
use Modules\Inventory\Jobs\ItemGroups\DeleteItemGroup;
use Modules\Inventory\Jobs\ItemGroups\UpdateItemGroup;
use Modules\Inventory\Http\Requests\ItemGroup as Request;
use Modules\Inventory\Http\Resources\ItemGroup as Resource;

class ItemGroups extends ApiController
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
        $item_groups = ItemGroup::with('category')->collect();

        return Resource::collection($item_groups);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item_groups = ItemGroup::with('category')->find($id);

        if (! $item_groups instanceof ItemGroup) {
            return $this->errorInternal('No query results for model [' . ItemGroup::class . '] ' . $id);
        }

        return new Resource($item_groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $item_group = $this->dispatch(new CreateItemGroup($request));
            
        return $this->created(route('api.item-groups.show', $item_group->id), new Resource($item_group));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $item
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ItemGroup $item_group, Request $request)
    {
        $item_group = $this->dispatch(new UpdateItemGroup($item_group, $request));

        return new Resource($item_group->fresh());
    }

    /**
     * Enable the specified resource in storage.
     *
     * @param  ItemGroup  $item_group
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(ItemGroup $item_group)
    {
        $item_group = $this->dispatch(new UpdateItemGroup($item_group, request()->merge(['enabled' => 1])));

        return new Resource($item_group->fresh());
    }

    /**
     * Disable the specified resource in storage.
     *
     * @param  ItemGroup  $item_group
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(ItemGroup $item_group)
    {
        $item_group = $this->dispatch(new UpdateItemGroup($item_group, request()->merge(['enabled' => 0])));

        return new Resource($item_group->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ItemGroup  $item_group
     * @return \Illuminate\Http\Response
     */
    public function destroy(ItemGroup $item_group)
    {
        try {
            $this->dispatch(new DeleteItemGroup($item_group));

            return $this->noContent();
        } catch(\Exception $e) {
            $this->errorUnauthorized($e->getMessage());
        }
    }
}
