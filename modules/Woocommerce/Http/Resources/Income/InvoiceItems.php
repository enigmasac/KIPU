<?php

namespace Modules\Woocommerce\Http\Resources\Income;

use App\Models\Common\Item;
use App\Models\Document\Document;
use App\Models\Module\Module;
use Modules\Woocommerce\Models\WooCommerceIntegration;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItems extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (isset($this->variation_id) && 0 !== $this->variation_id
            && null !== Module::alias('inventory')
                              ->enabled()
                              ->first()) {
            $itemIntegration = WooCommerceIntegration::where(
                [
                    'woocommerce_id' => $this->variation_id,
                    'item_type'      => \Modules\Inventory\Models\ItemGroupItem::class,
                ]
            )->first();

            if (null !== $itemIntegration) {
                $itemIntegration->item_id = $itemIntegration->item->item_id;
            }
        } else {
            $itemIntegration = WooCommerceIntegration::where(
                [
                    'woocommerce_id' => $this->product_id,
                    'item_type'      => Item::class,
                ]
            )->first();

            if (null === $itemIntegration || 0 === $this->product_id) {
                $item = Item::where('name', $this->name)->first();

                if (null !== $item) {
                    $itemIntegration          = new \stdClass();
                    $itemIntegration->item_id = $item->id;
                }
            }
        }

        return [
            'type'          => Document::INVOICE_TYPE,
            'de_account_id' => null, // Double-Entry compatibility
            'item_id'       => $itemIntegration->item_id ?? null,
            'name'          => $this->name ?? trans('general.na'),
            'description'   => $this->description ?? '',
            'price'         => $this->price,
            'quantity'      => $this->quantity,
            'tax_ids'       => $this->tax_ids ?? [],
            'company_id'    => company_id(),
            'created_from'  => source_name('woocommerce'),
        ];
    }
}
