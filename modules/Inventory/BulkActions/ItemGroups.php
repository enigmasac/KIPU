<?php

namespace Modules\Inventory\BulkActions;

use App\Abstracts\BulkAction;
use Modules\Inventory\Models\ItemGroup;
use Modules\Inventory\Jobs\ItemGroups\DeleteItemGroup;

class ItemGroups extends BulkAction
{
    public $model = ItemGroup::class;

    public $text = 'inventory::general.item_groups';

    public $path = [
        'group' => 'inventory',
        'type' => 'item-groups',
    ];

    public $actions = [
        'enable' => [
            'name' => 'general.enable',
            'message' => 'bulk_actions.message.enable',
            'path' =>  ['group' => 'inventory', 'type' => 'item-groups'],
            'type' => '*',
            'permission' => 'update-inventory-item-groups',
        ],
        'disable' => [
            'name' => 'general.disable',
            'message' => 'bulk_actions.message.disable',
            'path' =>  ['group' => 'inventory', 'type' => 'item-groups'],
            'type' => '*',
            'permission' => 'update-inventory-item-groups',
        ],
        'delete' => [
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'path' =>  ['group' => 'inventory', 'type' => 'item-groups'],
            'type' => '*',
            'permission' => 'delete-inventory-item-groups',
        ],
    ];

        /**
     * Enable the specified resource.
     *
     * @param  $request
     *
     * @return Response
     */
    public function enable($request)
    {
        $groups = $this->getSelectedRecords($request);

        foreach ($groups as $group) {
            $group->enabled = true;
            $group->save();

            foreach ($group->items as $group_item) {
                $item = $group_item->item;
                $item->enabled = true;
                $item->save();
            }
        }
    }

    /**
     * Disable the specified resource.
     *
     * @param  $request
     *
     * @return Response
     */
    public function disable($request)
    {
        $groups = $this->getSelectedRecords($request);

        foreach ($groups as $group) {
            $group->enabled = false;
            $group->save();

            foreach ($group->items as $group_item) {
                $item = $group_item->item;
                $item->enabled = false;
                $item->save();
            }
        }
    }

    public function destroy($request)
    {
        $items = $this->getSelectedRecords($request);

        foreach ($items as $item) {
            try {
                $this->dispatch(new DeleteItemGroup($item));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
