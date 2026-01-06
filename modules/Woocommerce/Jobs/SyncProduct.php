<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Http\Requests\Common\Item as ItemRequest;
use App\Jobs\Common\CreateItem;
use App\Jobs\Common\UpdateItem;
use App\Models\Common\Item;
use App\Traits\Modules;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Woocommerce\Http\Resources\Common\Item as CommonItem;
use Modules\Woocommerce\Http\Resources\Module\CustomFields;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Throwable;

class SyncProduct extends Job
{
    use Modules;

    protected $product;

    public function __construct($product)
    {
        $this->product = $product;

        parent::__construct($product);
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            $integration_params = [
                'company_id'     => company_id(),
                'woocommerce_id' => empty($this->product->id) ? 0 : $this->product->id,
                'item_type'      => Item::class,
            ];

            //woocommerce old data control
            $_product_name = $this->product->sku ?? ('product-' . $this->product->id);
            $_product      = Item::where('sku', $_product_name)->first();

            if (! empty($_product)) {
                $integration_params['item_id'] = $_product->id;
            }

            if (! empty($this->product->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $data = (array) (new CommonItem($this->product))->jsonSerialize();

            if ($this->moduleIsEnabled('custom-fields')) {
                if (! empty($this->product->meta_data)) {
                    $customFields = (array) (new CustomFields($this->product->meta_data))->jsonSerialize();

                    $data = array_merge($data, $customFields);

                    request()->merge($data);
                }
            }

            request()->merge($data);
            $request = (new ItemRequest())->merge($data);

            if ($integration->exists && null !== $integration->item) {
                $integration->item->fill($data);

                if ($this->moduleIsEnabled('inventory')) {
                    $inventory_item = \Modules\Inventory\Models\Item::where('item_id', $integration->item_id)->first();

                    if (null !== $inventory_item) {
                        $inventory_item->fill(
                            [
                                'sku'                 => $data['sku'],
                                'opening_stock'       => $data['items'][0]['opening_stock'],
                                'opening_stock_value' => $data['items'][0]['opening_stock_value'],
                                'reorder_level'       => $data['items'][0]['reorder_level'],
                                'default_warehouse'   => $data['items'][0]['default_warehouse'],
                            ]
                        );

                        if (false === $inventory_item->isDirty() && false === $integration->item->isDirty()) {
                            return;
                        }

                        $request->merge(
                            [
                                'items' => [
                                    collect($request->get('items')[0])
                                        ->merge(['id' => $inventory_item->id])
                                        ->toArray(),
                                ],
                            ]
                        );

                        $this->dispatch(new \Modules\Inventory\Jobs\Items\UpdateItem($integration->item, $request));
                    } else {
                        $item                 = $this->dispatch(new \Modules\Inventory\Jobs\Items\CreateItem($request));
                        $integration->item_id = $item->item_id;
                    }
                } elseif ($integration->item->isDirty()) {
                    $this->dispatch((new UpdateItem($integration->item, $request)));
                }
            } elseif ($this->moduleIsEnabled('inventory')) {
                $item                 = $this->dispatch(new \Modules\Inventory\Jobs\Items\CreateItem($request));
                $integration->item_id = $item->item_id;
            } else {
                $item                 = $this->dispatch((new CreateItem($request)));
                $integration->item_id = $item->id;
            }

            $integration->save();

            DB::commit();
        } catch (JsonException | Throwable $e) {
            Log::error(
                'WC Integration::: Exception:' . basename($e->getFile()) . ':' . $e->getLine() . ' - '
                . $e->getCode() . ': ' . $e->getMessage()
            );

            report($e);

            DB::rollBack();

            throw new Exception($e);
        }
    }
}
