<?php

namespace Modules\CompositeItems\Listeners\Update;

use App\Events\Install\UpdateFinished;
use App\Abstracts\Listeners\Update as Listener;
use Illuminate\Support\Facades\DB;
use App\Utilities\Date;

class Version200 extends Listener
{
    const ALIAS = 'composite-items';

    const VERSION = '2.0.0';

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

        DB::table('reports')->where('class', 'Modules\CompositeItems\Reports\SaleSummary')->update([
            'deleted_at' => Date::now()->toDateTimeString()
        ]);
    }
}
