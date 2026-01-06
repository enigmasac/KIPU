<?php

namespace Modules\Woocommerce\Listeners\Update\V30;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\File;

class Version307 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '3.0.7';

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

        File::deleteDirectory(base_path('modules/Woocommerce/Http/Controllers/Modals'));
        File::deleteDirectory(base_path('modules/Woocommerce/Resources/views/modals'));
    }
}
