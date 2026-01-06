<?php

namespace Modules\Woocommerce\Http\Resources\Income;

use App\Models\Document\Document;
use App\Models\Setting\Category;
use App\Traits\Documents;
use App\Utilities\Date;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class Invoice extends JsonResource
{
    use Documents;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $fieldMapping = collect(json_decode(setting('woocommerce.field_mapping', '')));

        $documentNumber = $this->getNextDocumentNumber(Document::INVOICE_TYPE);
        if ((null !== $mapping = $fieldMapping->where('field_id', 'document_number')->first())
            && null !== $metaData = collect($this->meta_data)->where('key', $mapping->wp_field)->first()) {
            $documentNumber = $metaData->value;
        }

        if (empty($this->billing->first_name) && empty($this->billing->last_name)) {
            $contact_name = $this->billing->email;
        } else {
            $contact_name = Str::limit(collect([$this->billing->first_name, $this->billing->last_name])->filter()->implode(' '));
        }

        return [
            'type'            => Document::INVOICE_TYPE,
            'company_id'      => company_id(),
            'status'          => 'paid',
            'document_number' => $documentNumber,
            'order_number'    => $this->number,
            'issued_at'       => Date::parse($this->date_created_gmt)->format('Y-m-d H:i:s'),
            'due_at'          => Date::parse($this->date_created_gmt)->format('Y-m-d H:i:s'),
            'amount'          => 0,
            'currency_code'   => $this->currency,
            'currency_rate'   => 1,
            'contact_name'    => $contact_name,
            'contact_email'   => $this->billing->email,
            'contact_phone'   => $this->billing->phone,
            'contact_address' => collect(
                [
                    $this->billing->address_1,
                    $this->billing->address_2,
                    $this->billing->city,
                    $this->billing->postcode,
                    $this->billing->country,
                ]
            )->filter()->implode("\r\n"),
            'category_id'     => $this->getCategoryId(),
            'created_from'    => source_name('woocommerce'),
        ];
    }

    protected function getCategoryId()
    {
        $category_id = setting('woocommerce.invoice_category_id');

        if (empty($category_id)) {
            $category_id = Category::where('type', 'income')->pluck('id')->first();
        }

        return $category_id;
    }
}
