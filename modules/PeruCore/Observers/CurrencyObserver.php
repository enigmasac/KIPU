<?php

namespace Modules\PeruCore\Observers;

use App\Models\Setting\Currency;

class CurrencyObserver
{
    /**
     * Handle the Currency "saving" event.
     *
     * @param  \App\Models\Setting\Currency  $currency
     * @return void
     */
    public function saving(Currency $currency)
    {
        // Si es Sol Peruano, la tasa es siempre 1
        if ($currency->code === 'PEN') {
            $currency->rate = 1.0;
            $currency->sunat_rate = 1.0;
            return;
        }

        // Si la tasa ingresada es mayor a 1 (ej. 3.75), asumimos formato peruano (Soles por 1 Moneda)
        // Guardamos el valor original en sunat_rate y el inverso en rate
        if ($currency->rate > 1) {
            $currency->sunat_rate = $currency->rate;
            $currency->rate = round(1 / $currency->rate, 10);
        } else {
            // Si la tasa es menor a 1, asumimos que ya es el inverso, o que no se ha configurado
            // En este caso, reconstruimos el sunat_rate para visualización
            $currency->sunat_rate = ($currency->rate > 0) ? round(1 / $currency->rate, 4) : 0;
        }
    }
}
