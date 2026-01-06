<?php

namespace Modules\Inventory\BulkActions;

use App\Abstracts\BulkAction;
use Modules\Inventory\Models\Variant;
use Modules\Inventory\Jobs\Variants\DeleteVariant;

class Variants extends BulkAction
{
    public $model = Variant::class;

    public $text = 'inventory::general.variants';

    public $path = [
        'group' => 'inventory',
        'type' => 'variants',
    ];

    public $actions = [
        'enable' => [
            'name' => 'general.enable',
            'message' => 'bulk_actions.message.enable',
            'path' =>  ['group' => 'inventory', 'type' => 'variants'],
            'type' => '*',
            'permission' => 'update-inventory-variants',
        ],
        'disable' => [
            'name' => 'general.disable',
            'message' => 'bulk_actions.message.disable',
            'path' =>  ['group' => 'inventory', 'type' => 'variants'],
            'type' => '*',
            'permission' => 'update-inventory-variants',
        ],
        'delete' => [
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'path' =>  ['group' => 'inventory', 'type' => 'variants'],
            'type' => '*',
            'permission' => 'delete-inventory-variants',
        ],
    ];

    public function destroy($request)
    {
        $items = $this->getSelectedRecords($request);

        foreach ($items as $item) {
            try {
                $this->dispatch(new DeleteVariant($item));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
