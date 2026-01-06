<?php

namespace Modules\Inventory\Listeners\Update;

use App\Events\Install\UpdateFinished;
use App\Abstracts\Listeners\Update as Listener;
use Illuminate\Support\Facades\DB;
use App\Utilities\Date;

class Version401 extends Listener
{
    const ALIAS = 'inventory';

    const VERSION = '4.0.1';

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

        $this->updateReport();
        $this->deleteReport();
    }

    protected function updateReport()
    {
        DB::table('reports')->where('class', 'Modules\Inventory\Reports\Item')->update([
            'deleted_at' => null
        ]);
    }

    protected function deleteReport()
    {
        DB::table('reports')->where('class', 'Modules\Inventory\Reports\ItemSummary')->update([
            'deleted_at' => Date::now()->toDateTimeString()
        ]);
    }
}
