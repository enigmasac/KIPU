<?php

namespace Modules\Inventory\Models;

use App\Abstracts\Model;
use Bkwld\Cloner\Cloneable;
use Modules\Inventory\Database\Factories\TransferOrder as TransferOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferOrder extends Model
{
    use Cloneable, HasFactory;

    protected $table = 'inventory_transfer_orders';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'date', 'transfer_order', 'transfer_order_number', 'reason', 'source_warehouse_id', 'destination_warehouse_id', 'status', 'created_from', 'created_by'];

    public $cloneable_relations = ['items'];

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item')->withDefault(['name' => trans('general.na')]);
    }

    public function source_warehouse()
    {
        return $this->belongsTo('Modules\Inventory\Models\Warehouse', 'source_warehouse_id', 'id')->withDefault(['name' => trans('general.na')]);
    }

    public function destination_warehouse()
    {
        return $this->belongsTo('Modules\Inventory\Models\Warehouse', 'destination_warehouse_id', 'id')->withDefault(['name' => trans('general.na')]);
    }

    public function transfer_order_items()
    {
        return $this->hasMany('Modules\Inventory\Models\TransferOrderItem');
    }

    public function histories()
    {
        return $this->hasMany('Modules\Inventory\Models\TransferOrderHistory');
    }

    public function getReadyAttribute()
    {
        return $this->histories->where('status', 'ready')->first();
    }

    public function getInTransferAttribute()
    {
        return $this->histories->where('status', 'in_transfer')->first();
    }

    public function getTransferredAttribute()
    {
        return $this->histories->where('status', 'transferred')->first();
    }

    public function getItemQuantityAttribute()
    {
        if (empty($this->item_id)) {
            return 0;
        }

        return \Modules\Inventory\Models\Item::where('warehouse_id', $this->source_warehouse_id)
            ->where('item_id', $this->item_id)
            ->value('opening_stock');
    }

    public function getStatusLabelAttribute(): string
    {
        switch ($this->status) {
            case 'draft':
                $status_label = 'status-draft';
                break;
            case 'ready':
                $status_label = 'status-sent';
                break;
            case 'in_transfer':
                $status_label = 'status-viewed';
                break;
            case 'transferred':
                $status_label = 'status-success';
                break;
            case 'cancelled':
                $status_label = 'status-canceled';
                break;
        }

        return $status_label;
    }

    /**
     * Get the line actions.
     *
     * @return array
     */
    public function getLineActionsAttribute()
    {
        $actions = [];

        $actions[] = [
            'title' => trans('general.show'),
            'icon' => 'visibility',
            'url' => route('inventory.transfer-orders.show', $this->id),
            'permission' => 'read-inventory-transfer-orders',
        ];

        $actions[] = [
            'title' => trans('general.edit'),
            'icon' => 'edit',
            'url' => route('inventory.transfer-orders.edit', $this->id),
            'permission' => 'update-inventory-transfer-orders',
        ];

        $actions[] = [
            'title' => trans('general.print'),
            'icon' => 'print',
            'url' => route('inventory.transfer-orders.print', $this->id),
            'permission' => 'update-inventory-transfer-orders',
        ];

        $actions[] = [
            'title' => trans('general.download_pdf'),
            'icon' => 'download',
            'url' => route('inventory.transfer-orders.download', $this->id),
            'permission' => 'update-inventory-transfer-orders',
        ];

        if (($this->status != 'cancelled')) {
            $actions[] = [
                'title' => trans('general.cancel'),
                'icon' => 'cancel',
                'url' => route('inventory.transfer-orders.cancelled', $this->id),
                'permission' => 'update-inventory-transfer-orders',
            ];
        };

        $actions[] = [
            'type' => 'delete',
            'title' => trans_choice('inventory::general.transfer_orders', 1),
            'icon' => 'delete',
            'route' => 'inventory.transfer-orders.destroy',
            'permission' => 'delete-inventory-transfer-orders',
            'model' => $this,
        ];

        return $actions;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransferOrderFactory::new();
    }
}
