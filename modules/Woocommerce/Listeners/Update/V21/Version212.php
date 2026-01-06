<?php

namespace Modules\Woocommerce\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\Artisan;

class Version212 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.1.2';

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

        if (null !== setting('woocommerce.custom_fields_mapping')) {
            setting()->set('woocommerce.field_mapping', setting('woocommerce.custom_fields_mapping'));
        }

        setting()->forget('woocommerce.custom_fields_mapping');

        setting()->save();
    }
}
