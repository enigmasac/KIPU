<?php

namespace Modules\PeruCore\Observers;

use App\Models\Common\Item;
use Illuminate\Support\Facades\DB;

class ItemObserver
{
    /**
     * Handle the Item "creating" event.
     */
    public function creating(Item $item)
    {
        if (empty($item->sku)) {
            $item->sku = $this->generateNextSku($item->company_id);
        }
    }

    /**
     * Handle the Item "saved" event to sync with Inventory table.
     */
    public function saved(Item $item)
    {
        // Si el módulo de inventario está activo, sincronizamos el SKU en su tabla
        if (!empty($item->sku)) {
            DB::table('inventory_items')
                ->where('item_id', $item->id)
                ->where('company_id', $item->company_id)
                ->update(['sku' => $item->sku]);
        }
    }

    /**
     * Generate a numeric correlative SKU.
     */
    protected function generateNextSku($company_id)
    {
        $lastItem = Item::where('company_id', $company_id)
            ->whereRaw("sku REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(sku AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $lastItem ? (int)$lastItem->sku + 1 : 1;

        return str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}