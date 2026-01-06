<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Models\Common\Item;
use App\Abstracts\Http\ApiController;
use Modules\Inventory\Http\Resources\Item as Resource;
use Modules\Inventory\Jobs\Items\CreateItem;
use Modules\Inventory\Jobs\Items\UpdateItem;
use Modules\Inventory\Http\Requests\Item as Request;
use Modules\Inventory\Jobs\Items\DeleteItem as InventoryDeleteItem;

class Items extends ApiController
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
        $items = Item::with('category', 'media')->collect();

        return Resource::collection($items);
    }

    /**
     * Display the specified resource.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = Item::with('category', 'taxes')->find($id);

        if (! $item instanceof Item) {
            return $this->errorInternal('No query results for model [' . Item::class . '] ' . $id);
        }

        return new Resource($item);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $item = $this->dispatch(new CreateItem($request));
            
        return $this->created(route('api.inventory-items.show', $item->id), new Resource($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  $item
     * @param  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Item $item, Request $request)
    {
        $item = $this->dispatch(new UpdateItem($item, $request));

        return new Resource($item->fresh());
    }

    /**
     * Enable the specified resource in storage.
     *
     * @param  Item  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Item $item)
    {
        $item = $this->dispatch(new UpdateItem($item, request()->merge(['enabled' => 1])));

        return new Resource($item->fresh());
    }

    /**
     * Disable the specified resource in storage.
     *
     * @param  Item  $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable(Item $item)
    {
        $item = $this->dispatch(new UpdateItem($item, request()->merge(['enabled' => 0])));

        return new Resource($item->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        try {
            $this->dispatch(new InventoryDeleteItem($item));

            return $this->noContent();
        } catch(\Exception $e) {
            $this->errorUnauthorized($e->getMessage());
        }
    }
}
