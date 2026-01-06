<?php

namespace Modules\CompositeItems\Observers;

use App\Traits\Jobs;
use App\Traits\Modules;
use App\Abstracts\Observer;
use App\Traits\Relationships;
use App\Models\Common\Item as Model;
use Modules\CompositeItems\Models\CompositeItem;
use Modules\CompositeItems\Jobs\DeleteCompositeItem;
use Modules\CompositeItems\Models\Item as ModuleItem;

class Item extends Observer
{
    use Relationships, Jobs, Modules;

    /**
     * Listen to the deleted event.
     *
     * @param  Model $item
     *
     * @return void
    */
    public function deleted(Model $item)
    {
        if (! $this->moduleIsEnabled('composite-items')) {
            return;
        }

        $composite_item_items = ModuleItem::where('item_id', $item->id)->get();

        if (empty($composite_item_items)) {
            return;
        }

        foreach ($composite_item_items as $composite_item_item) {
            $composite_items_count = ModuleItem::where('composite_item_id', $composite_item_item->composite_item_id)->count();

            if ($composite_items_count == 1) {
                $composite_item = CompositeItem::find($composite_item_item->composite_item_id);

                $this->dispatch(new DeleteCompositeItem($composite_item));
            } else {
                $composite_item_item->delete();
            }
        }
    }
}
