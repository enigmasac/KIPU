<?php

namespace Modules\Inventory\Models;

use App\Abstracts\Model;
use Bkwld\Cloner\Cloneable;
use Modules\Inventory\Database\Factories\Adjustment as AdjustmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Adjustment extends Model
{
    use Cloneable, HasFactory;

    protected $table = 'inventory_adjustments';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'date', 'adjustment_number', 'warehouse_id', 'description', 'reason', 'created_from', 'created_by'];

    public $cloneable_relations = ['items'];

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item')->withDefault(['name' => trans('general.na')]);
    }

    public function adjustment_items()
    {
        return $this->hasMany('Modules\Inventory\Models\AdjustmentItem');
    }

    public function warehouse()
    {
        return $this->belongsTo('Modules\Inventory\Models\Warehouse', 'warehouse_id', 'id')->withDefault(['name' => trans('general.na')]);
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
            'url' => route('inventory.adjustments.show', $this->id),
            'permission' => 'read-inventory-adjustments',
        ];

        $actions[] = [
            'title' => trans('general.edit'),
            'icon' => 'edit',
            'url' => route('inventory.adjustments.edit', $this->id),
            'permission' => 'update-inventory-adjustments',
        ];

        $actions[] = [
            'title' => trans('general.print'),
            'icon' => 'print',
            'url' => route('inventory.adjustments.print', $this->id),
            'permission' => 'update-inventory-adjustments',
        ];

        $actions[] = [
            'title' => trans('general.download_pdf'),
            'icon' => 'download',
            'url' => route('inventory.adjustments.download', $this->id),
            'permission' => 'update-inventory-adjustments',
        ];

        $actions[] = [
            'type' => 'delete',
            'title' => trans_choice('inventory::general.adjustments', 1),
            'icon' => 'delete',
            'route' => 'inventory.adjustments.destroy',
            'permission' => 'delete-inventory-adjustments',
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
        return AdjustmentFactory::new();
    }
}
