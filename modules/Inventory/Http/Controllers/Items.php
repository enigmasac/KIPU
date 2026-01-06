<?php

namespace Modules\Inventory\Http\Controllers;

use App\Traits\Currencies;
use App\Models\Common\Item;
use App\Models\Setting\Tax;
use App\Models\Setting\Category;
use App\Models\Setting\Currency;
use App\Abstracts\Http\Controller;
use Modules\Inventory\Models\History;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Traits\Barcode;
use Modules\Inventory\Models\Common\Item as CoreItem;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Jobs\Items\CreateItem;
use Modules\Inventory\Jobs\Items\UpdateItem;
use App\Jobs\Common\UpdateItem as BaseUpdateItem;
use Illuminate\Http\Request as IlluminateRequest;
use Modules\Inventory\Jobs\Items\GenerateBarcode;
use Illuminate\Notifications\DatabaseNotification;
use Modules\Inventory\Jobs\Histories\CreateHistory;
use Modules\Inventory\Exports\Items\Items as Export;
use Modules\Inventory\Http\Requests\Item as Request;
use Modules\Inventory\Imports\Items\Items as Import;
use App\Http\Requests\Common\Import as ImportRequest;
use Modules\Inventory\Jobs\Items\CreateInventoryItem;
use Modules\Inventory\Jobs\ItemGroups\CreateItemGroup;
use Modules\Inventory\Jobs\Items\DeleteItem as InventoryDeleteItem;
use Modules\Inventory\Exports\Show\Items\Histories as ExportHistories;

class Items extends Controller
{
    use Barcode, Currencies;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $items = CoreItem::with('category', 'media', 'inventories')->collect();

        return $this->response('inventory::items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $categories = Category::item()->enabled()->orderBy('name')->take(setting('default.select_limit'))->pluck('name', 'id');

        $taxes = Tax::enabled()->orderBy('name')->get()->pluck('title', 'id');

        $currency = Currency::where('code', setting('default.currency'))->first();

        $warehouses = Warehouse::enabled()->pluck('name', 'id');

        $variants = Variant::enabled()->orderBy('name')->pluck('name', 'id');

        foreach (json_decode(setting('inventory.units')) as $key => $value) {
            $units[$value] = $value;
        }

        return view('inventory::items.create', compact('units', 'categories', 'currency', 'warehouses', 'variants', 'taxes'));
    }

    public function store(Request $request)
    {
        if ($request->has('add_variants') == true && $request->get('add_variants') == 'true') {
            $request->merge(['items' => $request->group_items]);

            $response = $this->ajaxDispatch(new CreateItemGroup($request));

            if ($response['success']) {
                $response['redirect'] = route('inventory.items.index');
            }
        } else {
            $response = $this->ajaxDispatch(new CreateItem($request));

            if ($response['success']) {
                if (empty($request->get('track_inventory')) || $request->get('track_inventory') == 'false') {
                    $response['redirect'] = route('inventory.items.index');
                } else {
                    $response['redirect'] = route('inventory.items.show', $response['data']->id);
                }
            }
        }

        if ($response['success']) {
            $message = trans('messages.success.added', ['type' => trans_choice('general.items', 1)]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('inventory.items.create');

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Item  $item
     *
     * @return Response
     */
    public function duplicate(Item $item)
    {
        $clone = $item->duplicate();

        $inventory_items = $item->inventory()->get()->toArray();

        foreach ($inventory_items as $inventory_item) {
            $inventory_item['item_id'] = $clone->id;
            $inventory_item['sku'] = $inventory_item['sku'] . '-duplicate';

            $this->ajaxDispatch(new CreateInventoryItem($inventory_item));

            $history = [
                'company_id' => company_id(),
                'user_id' => user()->id,
                'item_id' => $clone->id,
                'type_id' => $clone->id,
                'type_type' => get_class($clone),
                'warehouse_id' => $inventory_item['warehouse_id'],
                'quantity' => $inventory_item['opening_stock'],
            ];

            $this->ajaxDispatch(new CreateHistory($history));
        }

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.items', 1)]);

        flash($message)->success();

        return redirect()->route('inventory.items.edit', $clone->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Item  $item
     *
     * @return Response
     */
    public function edit(Item $item)
    {
        $categories = Category::item()->enabled()->orderBy('name')->take(setting('default.select_limit'))->pluck('name', 'id');

        if ($item->category && !$categories->has($item->category_id)) {
            $categories->put($item->category->id, $item->category->name);
        }

        $warehouses = Warehouse::enabled()->pluck('name', 'id');

        $inventory_items = $item->inventory()->get();

        $sku = null;

        if (!empty($inventory_items[0])) {
            $sku = $inventory_items[0]->sku;
        }

        $track_control = !empty($inventory_items[0]) ? true : false;

        $taxes = Tax::enabled()->orderBy('name')->get()->pluck('title', 'id');

        foreach (json_decode(setting('inventory.units')) as $key => $value) {
            $units[$value] = $value;
        }

        return view('inventory::items.edit', compact('units', 'categories', 'item', 'inventory_items', 'sku', 'warehouses', 'track_control', 'taxes'));
    }

    public function update(Item $item, Request $request)
    {
        // Ensure is_detraction is treated as a toggle (0 if not present or false)
        if (!$request->has('is_detraction')) {
            $request->merge(['is_detraction' => 0]);
        }

        try {
            $response = $this->ajaxDispatch(new UpdateItem($item, $request));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for item update:', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error during item update: ' . $e->getMessage());
            throw $e;
        }

        if ($response['success']) {
            if (empty($request->get('track_inventory')) || $request->get('track_inventory') == 'false') {
                $response['redirect'] = route('inventory.items.index');
            } else {
                $response['redirect'] = route('inventory.items.show', $response['data']->id);
            }

            $message = trans('messages.success.updated', ['type' => $item->name]);

            flash($message)->success();
        } else {
            $response['redirect'] = route('inventory.items.edit', $item->id);

            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Item $item
     *
     * @return Response
     */
    public function destroy(Item $item)
    {
        $response = $this->ajaxDispatch(new InventoryDeleteItem($item));

        $response['redirect'] = route('inventory.items.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => $item->name]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return Response
     */
    public function show(Item $item)
    {
        $item_histories = History::where('item_id', $item->id)->collect();

        $document_histories = $item_histories->where('type_type', 'App\Models\Document\DocumentItem');

        $total_income = $total_expense = 0;

        foreach ($document_histories as $history) {
            $document_item = $history->document_item;

            if ($document_item && $document_item->type == 'invoice') {
                $total_income += $this->convertToDefault($document_item->total, $document_item->document->currency_code, $document_item->document->currency_rate);
            } else if ($document_item && $document_item->type == 'bill') {
                $total_expense += $this->convertToDefault($document_item->total, $document_item->document->currency_code, $document_item->document->currency_rate);
            }
        }

        $total_income = money($total_income, default_currency(), true);
        $total_expense = money($total_expense, default_currency(), true);

        $summary_amounts = [
            'income_exact' => $total_income->format(),
            'income_for_humans' => $total_income->formatForHumans(),
            'expense_exact' => $total_expense->format(),
            'expense_for_humans' => $total_expense->formatForHumans(),
        ];

        $item_histories = History::where('item_id', $item->id)->collect();

        $barcode = $this->getBarcodeMedia($item);

        return view('inventory::items.show', compact('item', 'item_histories', 'barcode', 'summary_amounts'));
    }

    /**
     * Enable the specified resource.
     *
     * @param  Item $item
     *
     * @return Response
     */
    public function enable(Item $item)
    {
        $response = $this->ajaxDispatch(new BaseUpdateItem($item, request()->merge(['enabled' => 1])));

        if ($response['success']) {
            $response['message'] = trans('messages.success.enabled', ['type' => $item->name]);
        }

        return response()->json($response);
    }

    /**
     * Disable the specified resource.
     *
     * @param  Item $item
     *
     * @return Response
     */
    public function disable(Item $item)
    {
        $response = $this->ajaxDispatch(new BaseUpdateItem($item, request()->merge(['enabled' => 0])));

        if ($response['success']) {
            $response['message'] = trans('messages.success.disabled', ['type' => $item->name]);
        }

        return response()->json($response);
    }

    /**
     * Import the specified resource.
     *
     * @param  ImportRequest  $request
     *
     * @return Response
     */
    public function import(ImportRequest $request)
    {
        $response = $this->importExcel(new Import, $request, trans_choice('general.items', 2));

        if ($response['success']) {
            $response['redirect'] = route('inventory.items.index');

            flash($response['message'])->success();
        } else {
            $response['redirect'] = route('import.create', ['inventory', 'items']);

            flash($response['message'])->error()->important();
        }

        return response()->json($response);
    }

    /**
     * Export the specified resource.
     *
     * @return Response
     */
    public function export()
    {
        return $this->exportExcel(new Export, trans_choice('general.items', 2));
    }

    public function markRead(IlluminateRequest $request)
    {
        $notification = DatabaseNotification::find($request->get('notification_id'));

        $notification->markAsRead();

        return redirect(route('notifications.index'));
    }

    public function markReadAll()
    {
        $notifications = DatabaseNotification::where('type', 'Modules\Inventory\Notifications\ItemReorderLevel')->get();

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        return redirect(route('notifications.index'));
    }

    /**
     * Export the specified resource.
     *
     * @return Response
     */
    public function exportHistory()
    {
        return $this->exportExcel(new ExportHistories, trans_choice('general.items', 1) . ' ' . trans_choice('inventory::general.histories', 2));
    }

    /**
     * Print the barcode.
     *
     * @param  Item $item
     *
     * @return Response
     */
    public function printBarcode(Item $item)
    {
        $barcode = $this->getBarcodeMedia($item);

        $view = view('inventory::partials.print_barcode.print_barcode', compact('item', 'barcode'));

        return mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
    }

    /**
     * Generate print barcode.
     *
     * @param  Item $item
     *
     * @return Response
     */
    public function generateBarcode(Item $item)
    {
        $barcode = $this->createBarcodeValue();

        $response = $this->ajaxDispatch(new GenerateBarcode($item, request()->merge(['barcode' => $barcode])));

        if ($response['success']) {
            $barcode = $this->getBarcodeMedia($response['data']);

            $item = $response['data'];

            $view = view('inventory::partials.print_barcode.print_barcode', compact('item', 'barcode'));

            return mb_convert_encoding($view, 'HTML-ENTITIES', 'UTF-8');
        } else {
            $response['redirect'] = route('inventory.items.show', $item->id);

            $message = $response['message'];

            flash($message)->error()->important();

            return response()->json($response);
        }
    }

    public function getBarcode(IlluminateRequest $request)
    {
        $barcode = $this->getBarcodeHtml($request['barcode_type'], $request['value']);

        return response()->json([
            'data' => [
                'html' => $barcode,
            ],
        ]);
    }
}
