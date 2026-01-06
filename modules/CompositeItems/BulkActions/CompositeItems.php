<?php

namespace Modules\CompositeItems\BulkActions;

use App\Abstracts\BulkAction;
use Modules\CompositeItems\Models\CompositeItem;
use Modules\CompositeItems\Jobs\DeleteCompositeItem;

class CompositeItems extends BulkAction
{
    public $model = CompositeItem::class;

    public $text = 'composite-items::general.composite_items';

    public $path = [
        'group' => 'composite-items',
        'type' => 'composite-items',
    ];

    public $actions = [
        'enable' => [
            'name' => 'general.enable',
            'message' => 'bulk_actions.message.enable',
            'path' =>  ['group' => 'composite-items', 'type' => 'composite-items'],
            'type' => '*',
            'permission' => 'update-composite-item-composite-item',
        ],
        'disable' => [
            'name' => 'general.disable',
            'message' => 'bulk_actions.message.disable',
            'path' =>  ['group' => 'composite-items', 'type' => 'composite-items'],
            'type' => '*',
            'permission' => 'update-composite-item-composite-item',
        ],
        'delete' => [
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'path' =>  ['group' => 'composite-items', 'type' => 'composite-items'],
            'type' => '*',
            'permission' => 'delete-composite-item-composite-item',
        ],
    ];

    public function destroy($request)
    {
        $items = $this->getSelectedRecords($request);

        foreach ($items as $item) {
            try {
                $this->dispatch(new DeleteCompositeItem($item));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
