<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Abstracts\Http\ApiController;
use Modules\Inventory\Models\History;
use Modules\Inventory\Http\Resources\History as Resource;

class Histories extends ApiController
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Add CRUD permission check
        $this->middleware('permission:read-common-companies')->only('index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $histories = History::orderBy('created_at')->collect('item.name');

        return Resource::collection($histories);
    }
}
