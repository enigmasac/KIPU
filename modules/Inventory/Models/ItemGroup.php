<?php

namespace Modules\Inventory\Models;

use App\Traits\Media;
use App\Abstracts\Model;
use Bkwld\Cloner\Cloneable;
use Illuminate\Notifications\Notifiable;
use Modules\Inventory\Database\Factories\ItemGroups;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemGroup extends Model
{
    use Cloneable, Notifiable, Media, HasFactory;

    protected $table = 'inventory_item_groups';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'name', 'description', 'category_id', 'enabled', 'created_from', 'created_by'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['name', 'description', 'enabled'];

    /**
     * Clonable relationships.
     *
     * @var array
     */
    public $cloneable_relations = ['items', 'variants', 'variant_values'];

    public function values()
    {
        return $this->hasMany('Modules\Inventory\Models\VariantValue');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Setting\Category');
    }

    public function variants()
    {
        return $this->hasMany('Modules\Inventory\Models\ItemGroupVariant');
    }

    public function variant_values()
    {
        return $this->hasMany('Modules\Inventory\Models\ItemGroupVariantValue');
    }

    public function items()
    {
        return $this->hasMany('Modules\Inventory\Models\ItemGroupItem')->with('values');
    }

    /**
     * Get the current balance.
     *
     * @return string
     */
    public function getPictureAttribute($value)
    {
        if (!empty($value) && !$this->hasMedia('picture')) {
            return $value;
        } elseif (!$this->hasMedia('picture')) {
            return false;
        }

        return $this->getMedia('picture')->last();
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
            'title' => trans('general.edit'),
            'icon' => 'edit',
            'url' => route('inventory.item-groups.edit', $this->id),
            'permission' => 'update-inventory-item-groups',
        ];

        $actions[] = [
            'title' => trans('general.duplicate'),
            'icon' => 'file_copy',
            'url' => route('inventory.item-groups.duplicate', $this->id),
            'permission' => 'create-inventory-item-groups',
        ];

        $actions[] = [
            'type' => 'delete',
            'title' => trans_choice('inventory::general.item_groups', 1),
            'icon' => 'delete',
            'route' => 'inventory.item-groups.destroy',
            'permission' => 'delete-inventory-item-groups',
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
        return ItemGroups::new();
    }
}
