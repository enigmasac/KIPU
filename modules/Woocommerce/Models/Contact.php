<?php

namespace Modules\Woocommerce\Models;

use App\Models\Common\Contact as Model;

class Contact extends Model
{
    /**
     * Get the contact's woocommerce item.
     */
    public function item()
    {
        return $this->morphOne(WooCommerceIntegration::class, 'item');
    }
}
