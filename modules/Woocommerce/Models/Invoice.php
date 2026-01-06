<?php

namespace Modules\Woocommerce\Models;

use App\Models\Document\Document as Model;

class Invoice extends Model
{
    /**
     * Get the invoice's woocommerce item.
     */
    public function item()
    {
        return $this->morphOne(WooCommerceIntegration::class, 'item');
    }
}
