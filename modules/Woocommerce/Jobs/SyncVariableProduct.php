<?php

namespace Modules\Woocommerce\Jobs;

use App\Abstracts\Job;
use App\Jobs\Common\CreateItem;
use App\Traits\Modules;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use Modules\Inventory\Http\Requests\ItemGroup as ItemGroupRequest;
use Modules\Inventory\Http\Requests\Variant as VariantRequest;
use Modules\Inventory\Jobs\ItemGroups\CreateItemGroup;
use Modules\Inventory\Jobs\ItemGroups\CreateItemGroupItem;
use Modules\Inventory\Jobs\ItemGroups\UpdateItemGroup;
use Modules\Inventory\Jobs\Items\UpdateItem;
use Modules\Inventory\Jobs\Variants\CreateVariant;
use Modules\Inventory\Jobs\Variants\CreateVariantValue;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemGroup;
use Modules\Inventory\Models\ItemGroupItem;
use Modules\Inventory\Models\Variant;
use Modules\Woocommerce\Http\Resources\Module\CustomFields;
use Modules\Woocommerce\Http\Resources\Module\InventoryItemGroup;
use Modules\Woocommerce\Http\Resources\Module\InventoryVariant;
use Modules\Woocommerce\Http\Resources\Module\InventoryVariantValue;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use stdClass;
use Throwable;

class SyncVariableProduct extends Job
{
    use Modules;

    protected $variable;

    public function __construct($variable)
    {
        $this->variable = $variable;

        parent::__construct($variable);
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
                'woocommerce_id' => empty($this->variable->id) ? 0 : $this->variable->id,
                'item_type'      => ItemGroup::class,
            ];

            if (! empty($this->variable->id)) {
                $integration = WooCommerceIntegration::firstOrNew($integration_params);
            } else {
                $integration = WooCommerceIntegration::make($integration_params);
            }

            $this->syncCustomProductAttributes($this->variable->attributes);

            $data = (array) (new InventoryItemGroup($this->variable))->jsonSerialize();

            if ($this->moduleIsEnabled('custom-fields')) {
                $customFields = (array) (new CustomFields($this->variable->meta_data))->jsonSerialize();

                $data = array_merge($data, $customFields);
            }

            if ($integration->exists && null !== $integration->item) {
                $this->syncItemGroupItems($this->variable, $integration->item, $data);
            }

            request()->merge($data);

            $request = (new ItemGroupRequest())->merge($data);

            if ($integration->exists && null !== $integration->item) {
                $this->dispatch((new UpdateItemGroup($integration->item, $request)));

                foreach ($integration->item->items as $inventoryOptionItem) {
                    $inventory_item = Item::where('item_id', $inventoryOptionItem->inventory_item->item_id)
                                          ->first();

                    if (null === $inventory_item) {
                        continue;
                    }

                    $inventoryItemData = collect($data['items'])->first(
                        function ($value) use ($inventory_item) {
                            return $value['item_id'] === $inventory_item->item_id;
                        }
                    );

                    if (null === $inventoryItemData) {
                        continue;
                    }

                    request()->merge($inventoryItemData);

                    $request = (new \Modules\Inventory\Http\Requests\Item())->merge($inventoryItemData);

                    $inventory_item->fill(
                        [
                            'sku'                 => $inventoryItemData['sku'],
                            'opening_stock'       => $inventoryItemData['items'][0]['opening_stock'],
                            'opening_stock_value' => $inventoryItemData['items'][0]['opening_stock_value'],
                            'reorder_level'       => $inventoryItemData['items'][0]['reorder_level'],
                            'default_warehouse'   => $inventoryItemData['items'][0]['default_warehouse'],
                        ]
                    );

                    if ($inventory_item->isDirty()) {
                        $request->merge(
                            [
                                'items' => [
                                    collect($request->get('items')[0])
                                        ->merge(['id' => $inventory_item->id])
                                        ->toArray(),
                                ],
                            ]
                        );

                        $this->dispatch(new UpdateItem($inventory_item->item, $request));
                    }
                }

            } else {
                $item = $this->dispatch((new CreateItemGroup($request)));

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

    private function syncCustomProductAttributes($attributes)
    {
        foreach ($attributes as $attribute) {
            if (0 !== $attribute->id && false === $attribute->variation) {
                continue;
            }

            $option = Variant::where('name', html_entity_decode($attribute->name))->first();
            if (null !== $option) {
                foreach ($attribute->options as $attributeOption) {
                    $optionValue = $option->values->where('name', html_entity_decode($attributeOption))->first();

                    if (null === $optionValue) {
                        $term            = new stdClass();
                        $term->name      = $attributeOption;
                        $term->option_id = $option->id;

                        $data = (array) (new InventoryVariantValue($term))->jsonSerialize();

                        $request = request()->merge($data);

                        $this->dispatch((new CreateVariantValue($request)));
                    }
                }
            } else {
                foreach ($attribute->options as $attributeOption) {
                    $attribute->items[] = [
                        'name' => $attributeOption,
                    ];
                }

                $data = (array) (new InventoryVariant($attribute))->jsonSerialize();

                request()->merge($data);
                $request = (new VariantRequest())->merge($data);

                $this->dispatch((new CreateVariant($request)));
            }
        }
    }

    private function syncItemGroupItems($variable, $itemGroup, &$data)
    {
        foreach ($variable->akaunting_items as $optionItem) {
            $integration = WooCommerceIntegration::firstOrNew(
                [
                    'company_id'     => company_id(),
                    'woocommerce_id' => $optionItem->id,
                    'item_type'      => ItemGroupItem::class,
                ]
            );

            $item_id = null;

            if ($integration->exists) {
                continue;
            }

            foreach ($data['items'] as &$variation) {
                if ($variation['wc_variation_id'] === $optionItem->id) {
                    break;
                }
            }

            $item = $this->dispatch(new CreateItem(array_merge($variation, ['company_id' => company_id()])));

            $inventory_item = Item::create(
                array_merge($variation, ['company_id' => company_id(), 'item_id' => $item->id])
            );

            //Create ItemGroupItem
            $option_item = [
                'company_id'    => company_id(),
                'item_id'       => $item->id,
                'item_group_id' => $itemGroup->id,
            ];

            $item_group_item = $this->dispatch(
                new CreateItemGroupItem($variation, $option_item)
            );

            $integration->item_id = $item_group_item->id;
            $integration->save();

            $variation['item_id'] = $item_group_item->item_id;
        }
    }
}
