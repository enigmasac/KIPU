<?php

namespace Modules\Woocommerce\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use App\Models\Common\Company;

class Version208 extends Listener
{
    const ALIAS = 'woocommerce';

    const VERSION = '2.0.8';

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

        $this->updateCompanies();

    }

    protected function updateCompanies()
    {
        $company_id = company_id();

        $companies = Company::cursor();

        foreach ($companies as $company) {
            $company->makeCurrent();

            $this->updateSettings();
        }

        optional(company($company_id))->makeCurrent();
    }

    public function updateSettings()
    {
        setting()->forget('woocommerce.account_id');
        setting()->save();
    }
}
