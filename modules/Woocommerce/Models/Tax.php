<?php

namespace Modules\Woocommerce\Models;

use App\Models\Setting\Tax as Model;

class Tax extends Model
{
    /**
     * Get the tax's woocommerce item.
     */
    public function item()
    {
        return $this->morphOne(WooCommerceIntegration::class, 'item');
    }
}
