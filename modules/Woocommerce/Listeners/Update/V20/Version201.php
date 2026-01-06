<?php

namespace Modules\Woocommerce\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\Artisan;

class Version201 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.0.1';

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

        Artisan::call('module:migrate', ['alias' => self::ALIAS, '--force' => true]);

        $this->updateSettings();
    }

    public function updateSettings()
    {
        setting()->forgetAll();
        setting()->load(true);

        setting()->forget('woocommerce.order_status_ids');

        setting()->save();
    }
}
