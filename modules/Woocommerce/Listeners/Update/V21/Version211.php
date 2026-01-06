<?php

namespace Modules\Woocommerce\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Version211 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.1.1';

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

        Artisan::call('module:migrate', ['alias' => self::ALIAS, '--force' => true]);

        $this->renameClasses();
        $this->deleteUnnecessaryJsFiles();
    }

    public function renameClasses()
    {
        DB::table('woocommerce_integrations')
          ->where('item_type', 'App\Models\Sale\Invoice')
          ->update(['item_type' => 'App\Models\Document\Document']);
    }

    public function deleteUnnecessaryJsFiles()
    {
        $files = [
            'modules/Woocommerce/Resources/assets/woocommerce.js',
            'modules/Woocommerce/Resources/assets/woocommerce.min.js',
        ];

        foreach ($files as $file) {
            File::delete(base_path($file));
        }
    }

}
