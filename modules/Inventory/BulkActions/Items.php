<?php

namespace Modules\Inventory\BulkActions;

use App\Abstracts\BulkAction;
use App\Models\Common\Item;
use App\Jobs\Common\DeleteItem;
use Modules\Inventory\Jobs\Items\DeleteItem as InventoryDeleteItem;

class Items extends BulkAction
{
    public $model = Item::class;

    public $text = 'general.items';

    public $path = [
        'group' => 'inventory',
        'type' => 'items',
    ];

    public $actions = [
        'enable' => [
            'name' => 'general.enable',
            'message' => 'bulk_actions.message.enable',
            'path' =>  ['group' => 'inventory', 'type' => 'items'],
            'type' => '*',
            'permission' => 'update-common-items',
        ],
        'disable' => [
            'name' => 'general.disable',
            'message' => 'bulk_actions.message.disable',
            'path' =>  ['group' => 'inventory', 'type' => 'items'],
            'type' => '*',
            'permission' => 'update-common-items',
        ],
        'delete' => [
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'path' =>  ['group' => 'inventory', 'type' => 'items'],
            'type' => '*',
            'permission' => 'delete-common-items',
        ],
    ];

    public function destroy($request)
    {
        $items = $this->getSelectedRecords($request);

        foreach ($items as $item) {
            try {
                $inventory_item = $item->inventory()->first();

                if (empty($inventory_item)) {
                    $this->dispatch(new DeleteItem($item));
                } else {
                    $this->dispatch(new InventoryDeleteItem($item));
                }
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
