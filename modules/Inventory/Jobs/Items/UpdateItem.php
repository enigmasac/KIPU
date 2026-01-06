<?php

namespace Modules\Inventory\Jobs\Items;

use App\Abstracts\Job;
use App\Models\Common\Company;
use App\Models\Common\Item;
use App\Interfaces\Job\ShouldUpdate;
use App\Jobs\Common\UpdateItem as CoreUpdateItem;;
use Modules\Inventory\Models\History;
use Modules\Inventory\Models\Item as InventoryItem;
use Modules\Inventory\Jobs\Histories\CreateHistory;
use Modules\Inventory\Jobs\Histories\UpdateHistory;
use Modules\Inventory\Jobs\Items\CreateInventoryItem;
use Modules\Inventory\Jobs\Items\UpdateInventoryItem;
use Modules\Inventory\Traits\Barcode;

class UpdateItem extends Job implements ShouldUpdate
{
    use Barcode;

    public function handle(): Item
    {
        //$this->authorize();

        \DB::transaction(function () {
            $this->model = $this->dispatch(new CoreUpdateItem($this->model, $this->request));

            if ($this->model->inventory()->value('barcode') != $this->request->barcode) {
                $this->setBarcode($this->model, $this->request->barcode);
            }

            if (!empty($this->request->items) && $this->request->get('track_inventory') == 'true') {
                foreach ($this->request->items as $request) {
                    if (isset($request['id'])) {
                        $request_item_id[] =  $request['id'];
                    }
                }

                $user = user();

                if (empty($user)) {
                    $company = Company::find($this->model->company_id);
                    $user = $company->users()->first();
                }

                foreach ($this->model->inventory()->pluck('id')->toArray() as $inventory_item_id) {
                    if (in_array($inventory_item_id, $request_item_id)) {
                        foreach ($this->request->items as $request_item) {
                            if ($request_item['default_warehouse'] == 'true') {
                                $request_item['default_warehouse'] = 1;
                            } elseif ($request_item['default_warehouse'] == 'false') {
                                $request_item['default_warehouse'] = 0;
                            }

                            if (in_array($request_item['warehouse_id'], $this->model->inventory()->pluck('warehouse_id')->toArray()) || isset($request_item['id'])) {
                                $inventory_item = InventoryItem::where('id', $request_item['id'])->first();

                                $opening_stock = $inventory_item->opening_stock;

                                if ($inventory_item->opening_stock_value > $request_item['opening_stock_value']) {
                                    $opening_stock -= $inventory_item->opening_stock_value - $request_item['opening_stock_value'];
                                } elseif ($inventory_item->opening_stock_value < $request_item['opening_stock_value']) {
                                    $opening_stock += $request_item['opening_stock_value'] - $inventory_item->opening_stock_value;
                                }

                                $inv_item_update_request = [
                                    'company_id' => company_id(),
                                    'item_id' => $this->model->id,
                                    'opening_stock' => $opening_stock,
                                    'opening_stock_value' => $request_item['opening_stock_value'],
                                    'reorder_level' => $request_item['reorder_level'] ?? 0,
                                    'warehouse_id' => $request_item['warehouse_id'],
                                    'default_warehouse' => $request_item['default_warehouse'],
                                    'sku' => $this->request->sku,
                                    'unit' => $this->request->unit,
                                    'returnable' => $this->request->returnable == 'true' ? 1 : 0,
                                    'barcode' => $this->request->barcode,
                                ];

                                $this->dispatch(new UpdateInventoryItem($inventory_item, $inv_item_update_request));

                                $history = History::where('warehouse_id', $request_item['warehouse_id'])
                                                  ->where('type_type', 'App\Models\Common\Item')
                                                  ->where('item_id', $inv_item_update_request['item_id'])
                                                  ->first();

                                $history_request = [
                                    'company_id' => $inv_item_update_request['company_id'],
                                    'user_id' => $user->id,
                                    'item_id' => $inv_item_update_request['item_id'],
                                    'type_id' => $inv_item_update_request['item_id'],
                                    'type_type' => 'App\Models\Common\Item',
                                    'warehouse_id' => $request_item['warehouse_id'],
                                    'quantity' => $request_item['opening_stock_value'],
                                ];

                                if (!$history){
                                    $this->dispatch(new CreateHistory($history_request));
                                } else{
                                    $this->dispatch(new UpdateHistory($history, $history_request));
                                }

                            } else {
                                $inv_item_create_request = [
                                    'company_id' => company_id(),
                                    'item_id' => $this->model->id,
                                    'opening_stock' => $request_item['opening_stock_value'],
                                    'opening_stock_value' => $request_item['opening_stock_value'],
                                    'reorder_level' => $request_item['reorder_level'] ?? 0,
                                    'warehouse_id' => $request_item['warehouse_id'],
                                    'default_warehouse' => $request_item['default_warehouse'],
                                    'sku' => $this->request->sku,
                                    'unit' => $this->request->unit,
                                    'returnable' => $this->request->returnable == 'true' ? 1 : 0,
                                    'barcode' => $this->request->barcode,
                                ];

                                $this->dispatch(new CreateInventoryItem($inv_item_create_request));

                                $history_request = [
                                    'company_id' => $inv_item_create_request['company_id'],
                                    'user_id' => $user->id,
                                    'item_id' => $this->model->id,
                                    'type_id' => $this->model->id,
                                    'type_type' => 'App\Models\Common\Item',
                                    'warehouse_id' => $request_item['warehouse_id'],
                                    'quantity' => $request_item['opening_stock'],
                                ];

                                $this->dispatch(new CreateHistory($history_request));
                            }
                        }
                    } else {
                        $inventory_item = InventoryItem::where('id', $inventory_item_id)->first();

                        $history = History::where('warehouse_id',  $inventory_item->warehouse_id)
                                            ->where('type_type', 'App\Models\Common\Item')
                                            ->where('item_id', $inventory_item->item_id)
                                            ->delete();

                        $inventory_item->delete();
                    }
                }

                $inventory_item = InventoryItem::where('item_id', $this->model->id)->first();

                if (empty($inventory_item)) {
                    foreach ($this->request->items as $request_item) {
                        if ($request_item['default_warehouse'] == 'true') {
                            $request_item['default_warehouse'] = 1;
                        } else if ($request_item['default_warehouse'] == 'false') {
                            $request_item['default_warehouse'] = 0;
                        }

                        $inv_item_create_request = [
                            'company_id' => company_id(),
                            'item_id' => $this->model->id,
                            'opening_stock' => $request_item['opening_stock'],
                            'opening_stock_value' => $request_item['opening_stock'],
                            'reorder_level' => $request_item['reorder_level'] ?? 0,
                            'warehouse_id' => $request_item['warehouse_id'],
                            'default_warehouse' => $request_item['default_warehouse'],
                            'sku' => $this->request->sku,
                            'unit' => $this->request->unit,
                            'returnable' => $this->request->returnable == 'true' ? 1 : 0,
                            'barcode' => $this->request->barcode,
                        ];

                        $this->dispatch(new CreateInventoryItem($inv_item_create_request));

                        $history_request = [
                            'company_id' => $inv_item_create_request['company_id'],
                            'user_id' => $user->id,
                            'item_id' => $this->model->id,
                            'type_id' => $this->model->id,
                            'type_type' => 'App\Models\Common\Item',
                            'warehouse_id' => $request_item['warehouse_id'],
                            'quantity' => $request_item['opening_stock'],
                        ];

                        $this->dispatch(new CreateHistory($history_request));
                    }
                }
            } else {
                InventoryItem::where('item_id', $this->model->id)->delete();
            }
        });

        return $this->model;
    }

    /**
     * Determine if this action is applicable.
     *
     * @return void
     */
    public function authorize()
    {
        if (!$relationships = $this->getRelationships()) {
            return;
        }

        if (!$this->request->get('enabled')) {
            $relationships[] = strtolower(trans_choice('general.companies', 1));

            $message = trans('messages.warning.disabled', ['name' => $this->manufacturer->name, 'text' => implode(', ', $relationships)]);

            throw new \Exception($message);
        }
    }

    public function getRelationships()
    {
        $rels = [
            'inventory_manufacturer_items'   => 'items',
            'inventory_manufacturer_vendors' => 'vendors',
        ];

        return $this->countRelationships($this->manufacturer, $rels);
    }
}
