<?php

namespace Modules\Inventory\Console;

use App\Traits\Jobs;
use App\Models\Common\Company;
use App\Models\Module\Module;
use App\Models\Common\Item;
use Illuminate\Console\Command;
use Modules\Inventory\Jobs\Items\ReminderReorderLevel;

class ItemReorderLevel extends Command
{
    use Jobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reorder_level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send item reorder level for inventory';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Disable model cache
        config(['laravel-model-caching.enabled' => false]);

        // Get all companies
        $company_ids = Module::allCompanies()->alias('inventory')->enabled()->pluck('company_id');

        foreach ($company_ids as $company_id) {
            $company = Company::find($company_id);

            if (null === $company) {
                continue;
            }

            $this->info('Sending inventory item reorder level for ' . $company->name . ' company.');

            // Set company
            $company->makeCurrent();

            //Don't send notification if disabled
            if (false === (bool) setting('inventory.reorder_level_notification')) {
                $this->info('Sending inventory item reorder level notification disabled by ' . $company->name . '.');

                continue;
            }

            $items = Item::cursor();

            foreach ($items as $item) {
                $this->dispatch(new ReminderReorderLevel($item));
            }
        }

        Company::forgetCurrent();
    }
}
