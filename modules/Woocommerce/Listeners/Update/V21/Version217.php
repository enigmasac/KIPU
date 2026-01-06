<?php

namespace Modules\Woocommerce\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\File;

class Version217 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.1.7';

    /**
     * Handle the event.
     *
     * @param  $event
     *
     * @return void
     */
    public function handle(UpdateFinished $event)
    {
        if ($this->skipThisUpdate($event)) {
            return;
        }

        $this->deleteOldFiles();
    }

    public function deleteOldFiles()
    {
        $files = [
            'modules/Woocommerce/Observers/Inventory/ItemGroupOptionItem.php',
        ];

        foreach ($files as $file) {
            File::delete(base_path($file));
        }
    }
}
