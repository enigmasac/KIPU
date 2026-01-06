<?php

namespace Modules\Inventory\Listeners\Update;

use App\Events\Install\UpdateFinished;
use App\Abstracts\Listeners\Update as Listener;
use Illuminate\Support\Facades\File;

class Version414 extends Listener
{
    const ALIAS = 'inventory';

    const VERSION = '4.1.4';

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

        File::delete(base_path('modules/Inventory/Models/CoreItem.php'));
    }
}
