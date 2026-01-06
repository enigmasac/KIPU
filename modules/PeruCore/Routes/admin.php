<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Ruta directa para evitar fallos de resolución de nombres
Route::get('{company_id}/peru-core-api/items/{item}', function ($company_id, $id = null) {
    if ($id === null) {
        $id = $company_id;
        $company_id = function_exists('company_id') ? company_id() : null;
    }

    $item = DB::table('items')->where('id', $id)->where('company_id', $company_id)->first();
    
    if (!$item) {
        return response()->json(['sku' => '', 'sunat_unit_code' => 'NIU']);
    }

    $inventory_sku = DB::table('inventory_items')->where('item_id', $id)->where('company_id', $company_id)->value('sku');
    
    return response()->json([
        'sku' => (string) ($inventory_sku ?: ($item->sku ?: '')),
        'sunat_unit_code' => (string) ($item->sunat_unit_code ?: 'NIU'),
    ]);
})->middleware(['admin']);
