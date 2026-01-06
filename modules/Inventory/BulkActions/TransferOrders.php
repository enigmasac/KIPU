<?php

namespace Modules\Inventory\BulkActions;

use App\Abstracts\BulkAction;
use Modules\Inventory\Models\TransferOrder;
use Modules\Inventory\Jobs\TransferOrders\DeleteTransferOrder;

class TransferOrders extends BulkAction
{
    public $model = TransferOrder::class;

    public $text = 'inventory::general.transfer_orders';

    public $path = [
        'group' => 'inventory',
        'type' => 'transfer-orders',
    ];

    public $actions = [
        'delete' => [
            'name' => 'general.delete',
            'message' => 'bulk_actions.message.delete',
            'path' =>  ['group' => 'inventory', 'type' => 'transfer-orders'],
            'type' => '*',
            'permission' => 'delete-inventory-transfer-orders',
        ],
    ];

    public function destroy($request)
    {
        $items = $this->getSelectedRecords($request);

        foreach ($items as $item) {
            try {
                $this->dispatch(new DeleteTransferOrder($item));
            } catch (\Exception $e) {
                flash($e->getMessage())->error()->important();
            }
        }
    }
}
