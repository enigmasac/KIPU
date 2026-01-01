<?php

namespace App\Jobs\Document;

use App\Abstracts\Job;
use App\Interfaces\Job\HasOwner;
use App\Interfaces\Job\HasSource;
use App\Interfaces\Job\ShouldCreate;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem;
use App\Models\Document\DocumentItemTax;
use App\Models\Setting\Tax;
use Illuminate\Support\Str;

class CreateDocumentItem extends Job implements HasOwner, HasSource, ShouldCreate
{
    protected $document;

    protected $request;

    public function __construct(Document $document, $request)
    {
        $this->document = $document;
        $this->request = $request;

        parent::__construct($document, $request);
    }

    public function handle(): DocumentItem
    {
        $item_id = ! empty($this->request['item_id']) ? $this->request['item_id'] : 0;
        $precision = currency($this->document->currency_code)->getPrecision();

        $item_amount = (double) $this->request['price'] * (double) calculation_to_quantity($this->request['quantity']);

        $item_discounted_amount = $item_amount;

        // Apply line discount to amount
        if (! empty($this->request['discount'])) {
            if ($this->request['discount_type'] === 'percentage') {
                $item_discounted_amount -= ($item_amount * ($this->request['discount'] / 100));
            } else {
                $item_discounted_amount -= $this->request['discount'];
            }
        }

        // Apply total discount to amount
        if (! empty($this->request['global_discount'])) {
            if ($this->request['global_discount_type'] === 'percentage') {
                $global_discount = $item_discounted_amount * ($this->request['global_discount'] / 100);
            } else {
                $global_discount = $this->request['global_discount'];
            }

            $item_discounted_amount -= $global_discount;
        }

        $tax_amount = 0;
        $item_tax_total = 0;
        $actual_price_item = $item_amount = $item_discounted_amount;

        $item_taxes = [];
        $doc_params = [
            'company_id'    => $this->document->company_id,
            'type'          => $this->document->type,
            'document_id'   => $this->document->id,
        ];

        if (!empty($this->request['tax_ids'])) {
            $taxes_to_apply = [];
            foreach ((array) $this->request['tax_ids'] as $tax_id) {
                $tax = Tax::find($tax_id);
                if ($tax) {
                    $taxes_to_apply[] = $tax;
                }
            }

            // Group taxes by priority
            $grouped_taxes = collect($taxes_to_apply)->groupBy('priority')->sortKeys();

            $current_base = $actual_price_item; // Starting with discounted net price
            $total_tax_amount = 0;

            foreach ($grouped_taxes as $priority => $priority_taxes) {
                $priority_tax_sum = 0;
                $base_for_this_priority = $current_base;

                foreach ($priority_taxes as $tax) {
                    $tax_amount = 0;

                    switch ($tax->type) {
                        case 'inclusive':
                            // Inclusive is special, usually applied on the initial net.
                            // But following the "priority" logic, we'll treat it as part of the base calculation.
                            $tax_amount = $base_for_this_priority - ($base_for_this_priority / (1 + $tax->rate / 100));
                            break;
                        case 'fixed':
                            $tax_amount = $tax->rate * (double) $this->request['quantity'];
                            break;
                        case 'withholding':
                            $tax_amount = -($base_for_this_priority * ($tax->rate / 100));
                            break;
                        case 'compound':
                            // Compound in Akaunting is usually calculated on (Net + previous taxes)
                            // Which matches our priority logic if compound taxes have higher priority.
                            $tax_amount = ($base_for_this_priority * ($tax->rate / 100));
                            break;
                        default: // normal
                            $tax_amount = $base_for_this_priority * ($tax->rate / 100);
                            break;
                    }

                    $item_taxes[] = $doc_params + [
                        'tax_id' => $tax->id,
                        'name' => $tax->name,
                        'amount' => $tax_amount,
                    ];

                    $priority_tax_sum += $tax_amount;
                }

                // Update base for the next priority level
                $current_base += $priority_tax_sum;
                $total_tax_amount += $priority_tax_sum;
            }

            $actual_price_item = $item_discounted_amount + $total_tax_amount;
            $item_tax_total = $total_tax_amount;
        }

        if (! empty($global_discount)) {
            $actual_price_item += $global_discount;
            $item_amount += $global_discount; 
            $item_discounted_amount += $global_discount;
        }

        $this->request['company_id'] = $this->document->company_id;
        $this->request['type'] = $this->document->type;
        $this->request['document_id'] = $this->document->id;
        $this->request['item_id'] = $item_id;
        $this->request['name'] = Str::limit($this->request['name'], 180, '');
        $this->request['description'] = ! empty($this->request['description']) ? $this->request['description'] : '';
        $this->request['quantity'] = (double) calculation_to_quantity($this->request['quantity']);
        $this->request['price'] = round($this->request['price'], $precision);
        $this->request['tax'] = round($item_tax_total, $precision);
        $this->request['discount_type'] = ! empty($this->request['discount_type']) ? $this->request['discount_type'] : 'percent';
        $this->request['discount_rate'] = ! empty($this->request['discount']) ? $this->request['discount'] : 0;
        $this->request['total'] = round($actual_price_item, $precision);
        $this->request['created_from'] = $this->request['created_from'];
        $this->request['created_by'] = $this->request['created_by'];

        $document_item = DocumentItem::create($this->request);

        $document_item->item_taxes = false;
        $document_item->inclusives = false;
        $document_item->compounds = false;

        if (!empty($item_taxes)) {
            $document_item->item_taxes = $item_taxes;
            $document_item->inclusives = $inclusives ?? null;
            $document_item->compounds = $compounds ?? null;

            foreach ($item_taxes as $item_tax) {
                $item_tax['document_item_id'] = $document_item->id;
                $item_tax['amount'] = round(abs($item_tax['amount']), $precision);
                $item_tax['created_from'] = $this->request['created_from'];
                $item_tax['created_by'] = $this->request['created_by'];

                DocumentItemTax::create($item_tax);
            }
        }

        return $document_item;
    }
}
