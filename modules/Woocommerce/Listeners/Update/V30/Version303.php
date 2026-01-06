<?php

namespace Modules\Woocommerce\Listeners\Update\V30;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use App\Interfaces\Listener\ShouldUpdateAllCompanies;

class Version303 extends Listener implements ShouldUpdateAllCompanies
{
    const ALIAS = 'woocommerce';

    const VERSION = '3.0.3';

    /**
     * Handle the event.
     *
     * @param  $event
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        if (! is_array(json_decode(setting('woocommerce.order_status_ids')))) {
            setting()->forget('woocommerce.order_status_ids');
            setting()->save();
        }
    }
}
