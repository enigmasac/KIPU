<?php

namespace Modules\Woocommerce\Models;

use App\Abstracts\Model;

class WooCommerceIntegration extends Model
{
    protected $table = 'woocommerce_integrations';
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'woocommerce_id', 'item_id', 'item_type', 'woocommerce_response', 'akaunting_request'];

    /**
     * Get the owning item model.
     */
    public function item()
    {
        return $this->morphTo();
    }
}
