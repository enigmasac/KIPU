<?php

namespace Modules\Woocommerce\Http\Resources\Income;

use App\Models\Document\Document;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceTotals extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'type'         => Document::INVOICE_TYPE,
            'amount'       => $this->amount,
            'code'         => $this->code,
            'name'         => $this->name,
            'company_id'   => company_id(),
            'sort_order'   => $this->sort_order,
            'created_from' => source_name('woocommerce'),
        ];
    }
}
