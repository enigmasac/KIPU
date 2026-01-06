<?php

namespace Modules\CompositeItems\Listeners\Update;

use App\Events\Install\UpdateFinished;
use App\Abstracts\Listeners\Update as Listener;
use Illuminate\Support\Facades\DB;
use App\Utilities\Date;

class Version204 extends Listener
{
    const ALIAS = 'composite-items';

    const VERSION = '2.0.4';

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

        $composite_items = DB::table('composite_items_composite_items')->get();

        foreach ($composite_items as $composite_item) {
            $item = DB::table('items')->where('id', $composite_item->item_id)->first();

            if (empty($item)) {
                DB::table('composite_items_composite_items')->where('id', $composite_item->id)->update([
                    'deleted_at' => Date::now()->toDateTimeString()
                ]);
            }
        }
    }
}
