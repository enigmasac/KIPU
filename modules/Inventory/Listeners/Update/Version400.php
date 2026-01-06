<?php

namespace Modules\Inventory\Listeners\Update;

use App\Events\Install\UpdateFinished;
use App\Abstracts\Listeners\Update as Listener;
use Illuminate\Support\Facades\DB;
use App\Utilities\Date;

class Version400 extends Listener
{
    const ALIAS = 'inventory';

    const VERSION = '4.0.0';

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

        $this->updateWidgets();
        $this->deleteReport();
    }

    protected function updateWidgets()
    {
        DB::table('widgets')->where('class', 'like', 'Modules\\\\Inventory\\\\Widgets%')->update([
            'settings' => ['width' => 'w-full lg:w-auto']
        ]);
    }

    protected function deleteReport()
    {
        DB::table('reports')->where('class', 'Modules\Inventory\Reports\Item')->update([
            'deleted_at' => Date::now()->toDateTimeString()
        ]);
    }
}
