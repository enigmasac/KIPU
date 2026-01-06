<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use App\Http\Requests\Document\Document;
use App\Models\Document\DocumentItem;
use Modules\Inventory\Models\Item as InventoryItem;
use Modules\Inventory\Services\Validation as ServiceValidation;

class Validation extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $request = $this->app['request'];

        if ($request->segment(3) != 'invoices') {
            return;
        }

        $this->app['validator']->resolver(function ($translator, $data, $rules, $messages, $attributes) {
            if (isset($data['items'])) {
                foreach ($data['items'] as $key => $value) {
                    if (!empty($rules['items.' . $key  . '.quantity'])) {
                        $rules['items.' . $key  . '.quantity'].= '|quantity';
                    }
        
                    if (!empty($rules['quantity'])) {
                        $rules['quantity'].= '|quantity';
                    }
                }
            }

            return new ServiceValidation($translator, $data, $rules, $messages, $attributes);
        });

        $this->app['validator']->extend('quantity', function ($attribute, $value, $parameters, &$validator) use ($request) {
            $status = true;

            if (setting('inventory.negative_stock')) {
                return $status;
            }

            $attibutes = explode('.', $attribute);

            $item = $request['items'][$attibutes[1]];

            if (empty($item['warehouse_id'])) {
                return $status;
            }

            $inventory_item = InventoryItem::where('item_id', $item['item_id'])
                                            ->where('warehouse_id', $item['warehouse_id'])
                                            ->first();

            if (! $inventory_item) {
                return $status;
            }

            $stock = 0;

            if ($inventory_item->opening_stock) {
                $stock = $inventory_item->opening_stock;
            }
            
            if ($request->segment(4)){
                $stock += DocumentItem::where('document_id', $request->segment(4))->where('item_id', $item['item_id'])->sum('quantity');
                
                $new_quantity = 0;
                
                foreach ($request['items'] as $value) {
                    if ($value['item_id'] == $item['item_id']) {
                        $new_quantity += $value['quantity'];
                    }
                }

                $item['quantity'] = $new_quantity;
            }

            if ($item['quantity'] > $stock) {
                $status = false;

                $validator->fallbackMessages['quantity'] = trans('inventory::general.invalid_stock', ['stock' => $stock]);
            }

            return $status;
        },
            trans('inventory::general.invalid_stock', ['stock' => 0])
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
