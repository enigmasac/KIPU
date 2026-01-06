<?php

namespace Modules\Woocommerce\Listeners\Update\V21;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Version2110 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.1.10';

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

        $this->renameClasses();
        $this->deleteOldFiles();
    }

    public function renameClasses()
    {
        DB::table('woocommerce_integrations')
          ->where('item_type', 'Modules\Inventory\Models\Option')
          ->update(['item_type' => 'Modules\Inventory\Models\Variant']);

        DB::table('woocommerce_integrations')
          ->where('item_type', 'Modules\Inventory\Models\OptionValue')
          ->update(['item_type' => 'Modules\Inventory\Models\VariantValue']);

        DB::table('woocommerce_integrations')
          ->where('item_type', 'Modules\Inventory\Models\ItemGroupOption')
          ->update(['item_type' => 'Modules\Inventory\Models\ItemGroupVariant']);

        DB::table('woocommerce_integrations')
          ->where('item_type', 'Modules\Inventory\Models\ItemGroupOptionValue')
          ->update(['item_type' => 'Modules\Inventory\Models\ItemGroupVariantValue']);

    }

    public function deleteOldFiles()
    {
        $files = [
            'modules/Woocommerce/Transformers/Module/InventoryOption.php',
            'modules/Woocommerce/Transformers/Module/InventoryOptionValue.php',
            'modules/Woocommerce/Observers/Inventory/Option.php',
            'modules/Woocommerce/Observers/Inventory/OptionValue.php',
        ];

        foreach ($files as $file) {
            File::delete(base_path($file));
        }
    }
}
