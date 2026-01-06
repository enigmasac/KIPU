<?php

namespace Modules\Inventory\Jobs\Items;

use App\Abstracts\JobShouldQueue;
use Modules\Inventory\Notifications\ItemReorderLevel as Notification;

class ReminderReorderLevel extends JobShouldQueue
{
    protected $item;

    /**
     * Create a new job instance.
     *
     * @param  $item
     *
     */
    public function __construct($item)
    {
        $this->item = $item;

        $this->onQueue('inventory');
    }

    public function handle()
    {
        $inventory_items = $this->item->inventory()->get();

        if (empty($inventory_items)) {
            return;
        }

        foreach ($inventory_items as $inventory_item) {
            if ($inventory_item->opening_stock <= $inventory_item->reorder_level) {
                try {
                    foreach (company()->users as $user) {
                        if (!$user->can('read-notifications')) {
                            continue;
                        }

                        $reads = $user->Notifications;

                        $duplicate = false;

                        foreach ($reads as $read) {
                            $data = $read->getAttribute('data');

                            if (isset($data['inventory_item_ids']) == false){
                                continue;
                            }

                            if ($data['inventory_item_id'] == $inventory_item->id) {
                                $duplicate = true;
                            }
                        }

                        if ($duplicate == true) {
                            continue;
                        }

                        $user->notify(new Notification($inventory_item));
                    }
                } catch (\Throwable $e) {
                    report($e);

                    continue;
                }
            }
        }        
    }
}
