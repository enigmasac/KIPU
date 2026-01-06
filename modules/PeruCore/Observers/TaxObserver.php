<?php

namespace Modules\PeruCore\Observers;

use App\Models\Setting\Tax;

class TaxObserver
{
    /**
     * Handle the Tax "deleting" event.
     *
     * @param  \App\Models\Setting\Tax  $tax
     * @return void
     * @throws \Exception
     */
    public function deleting(Tax $tax)
    {
        if (!empty($tax->sunat_code)) {
            throw new \Exception("No se puede eliminar un impuesto base de SUNAT (Código: {$tax->sunat_code}). Es vital para la facturación electrónica.");
        }
    }
}
