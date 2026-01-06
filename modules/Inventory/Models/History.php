<?php

namespace Modules\Inventory\Models;

use App\Abstracts\Model;
use Bkwld\Cloner\Cloneable;
use Illuminate\Database\Eloquent\Builder;

class History extends Model
{
    use Cloneable;

    protected $table = 'inventory_histories';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['item', 'warehouse'];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'user_id', 'item_id', 'warehouse_id', 'type_id', 'type_type', 'quantity', 'created_from', 'created_by'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    protected $sortable = ['created_at', 'item.name', 'warehouse.name', 'type_type', 'quantity',];

    public function type()
    {
        return $this->morphTo();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Common\Item')->withDefault(['name' => trans('general.na')]);
    }

    public function warehouse()
    {
        return $this->belongsTo('Modules\Inventory\Models\Warehouse')->withDefault(['name' => trans('general.na')]);
    }

    public function document_item()
    {
        return $this->belongsTo('App\Models\Document\DocumentItem', 'type_id', 'id');
    }

    public function getActionTextAttribute()
    {
        $types = explode("\\", $this->type_type);
        $type = end($types);

        switch ($type) {
            case 'Item':
                return trans('inventory::items.created_item');
                break;
            case 'DocumentItem':
                if (isset($this->type->document->document_number)) {
                    return $this->type->document->document_number;
                }
                break;
            case 'TransferOrder':
                if (isset($this->type->transfer_order_number)) {
                    return $this->type->transfer_order_number;
                }
                break;
            case 'Adjustment':
                if (isset($this->type->adjustment_number)) {
                    return $this->type->adjustment_number;
                }
                break;
        }
    }

    public function getActionTypeAttribute()
    {
        $types = explode("\\", $this->type_type);
        $type = end($types);

        switch ($type) {
            case 'Item':
                return trans('inventory::items.created_item');
                break;
            case 'DocumentItem':
                if (isset($this->type->document->type)) {
                    if ($this->type->document->type == 'invoice') {
                        return trans_choice('general.invoices', 1);
                    } else if ($this->type->document->type == 'bill') {
                        return trans_choice('general.bills', 1);
                    } else if ($this->type->document->type == 'credit-note') {
                        return trans_choice('credit-debit-notes::general.credit_notes', 1);
                    } else if ($this->type->document->type == 'debit-note') {
                        return trans_choice('credit-debit-notes::general.debit_notes', 1);
                    } else if ($this->type->document->type == 'pos-order') {
                        return trans_choice('pos::general.orders', 1);
                    } else {
                        return 'N/A';
                    }
                }
                break;
            case 'TransferOrder':
                return trans_choice('inventory::general.transfer_orders', 1);

                break;
            case 'Adjustment':
                return trans_choice('inventory::general.adjustments', 1);
                break;
        }
    }

    public function getOperationTypeAttribute()
    {
        $types = explode("\\", $this->type_type);
        $type = end($types);

        switch ($type) {
            case 'Item':
                return;
                break;
            case 'DocumentItem':
                if (isset($this->type->document->type)) {
                    $operation_type = config('type.operation.' . $this->type->document->type);

                    if ($operation_type && $operation_type == 'negative') {
                        return $type = '-';
                    }
                }

                break;
            case 'TransferOrder':
                if (isset($this->type->source_warehouse_id)) {
                    if ($this->type->source_warehouse_id == $this->warehouse_id) {
                        return $type = '-';
                    }
                }

                if (isset($this->type->destination_warehouse_id)) {
                    if ($this->type->destination_warehouse_id == $this->warehouse_id) {
                        return;
                    }
                }
                break;
            case 'Adjustment':
                return $type = '-';
                break;
        }
    }

    public function getActionUrlAttribute()
    {
        $types = explode("\\", $this->type_type);
        $type = end($types);

        $url = '#';

        if (!isset($this->type->id)) {
            return $url;
        }

        switch ($type) {
            case 'Item':
                $url = company_id() . '/inventory/items/' . $this->type->id;
                break;
            case 'DocumentItem':
                if (! isset($this->type->document->type)) {
                    return;
                }

                $document_type = $this->type->document->type;

                $route = '';
                $parameter = $this->type->document->id;
                $alias = config('type.document.' . $document_type . '.alias');
                $prefix = config('type.document.' . $document_type . '.route.prefix');

                // if use module set module alias
                if (!empty($alias)) {
                    $route .= $alias . '.';
                }

                if (!empty($prefix)) {
                    $route .= $prefix . '.';
                }

                $route .= 'show';

                $url = route($route, $parameter);

                break;
            case 'TransferOrder':
                $url = route('inventory.transfer-orders.show', $this->type->id);
                break;
            case 'Adjustment':
                $url = route('inventory.adjustments.show', $this->type->id);
                break;
        }

        return $url;
    }

    public function getNumberAttribute()
    {
        $types = explode("\\", $this->type_type);
        $type = end($types);

        switch ($type) {
            case 'Item':
                return trans('inventory::items.created_item');
                break;
            case 'DocumentItem':
                if (isset($this->type->document->document_number)) {
                    return $this->type->document->document_number;
                }
                break;
            case 'TransferOrder':
                return $this->type->transfer_order_number;
                break;
            case 'Adjustment':
                return $this->type->adjustment_number;
                break;
        }

        return;
    }

    public function getActionRouteAttribute()
    {
        $types = explode("\\", $this->type_type);
        $type = end($types);

        $routes = [];

        if (! isset($this->type->id)) {
            return $routes;
        }

        switch ($type) {
            case 'Item':
                $routes = [
                    'inventory.items.show',
                    $this->type->id,
                ];

                break;
            case 'DocumentItem':
                if (! isset($this->type->document->type)) {
                    return;
                }
                
                $document_type = $this->type->document->type;

                $route = '';
                $parameter = $this->type->document->id;
                $alias = config('type.document.' . $document_type . '.alias');
                $prefix = config('type.document.' . $document_type . '.route.prefix');

                // if use module set module alias
                if (!empty($alias)) {
                    $route .= $alias . '.';
                }

                if (!empty($prefix)) {
                    $route .= $prefix . '.';
                }

                $route .= 'show';

                $routes = [$route, $parameter];
                break;
        }

        return $routes;
    }

    public function scopeDocument(Builder $query)
    {
        return $query->where($this->qualifyColumn('type_type'), '=', 'App\Models\Document\DocumentItem');
    }
}
