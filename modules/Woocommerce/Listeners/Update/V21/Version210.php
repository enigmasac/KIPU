<?php

namespace Modules\Woocommerce\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Version210 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.1.0';

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

        $this->deleteOldFiles();
    }

    public function deleteOldFiles()
    {
        $files = [
            'modules/Woocommerce/Listeners/Update/Version200.php',
            'modules/Woocommerce/Listeners/Update/Version201.php',
            'modules/Woocommerce/Listeners/Update/Version208.php',
            'modules/Woocommerce/0.js',
            'modules/Woocommerce/1.js',
        ];

        foreach ($files as $file) {
            File::delete(base_path($file));
        }
    }
}
