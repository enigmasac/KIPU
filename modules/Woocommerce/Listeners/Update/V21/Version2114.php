<?php

namespace Modules\Woocommerce\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\DB;

class Version2114 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.1.14';

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

        $this->updateWoocommerceUrls();
    }

    public function updateWoocommerceUrls()
    {
        $urls = DB::table('settings')->where('key', 'woocommerce.url')->cursor();

        foreach ($urls as $url) {
            $newUrl = str_replace(['http://', 'https://'], '', $url->value);

            DB::table('settings')
              ->where('id', $url->id)
              ->update(['value' => $newUrl]);
        }
    }
}
