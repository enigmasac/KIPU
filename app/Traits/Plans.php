<?php

namespace App\Traits;

use App\Traits\SiteApi;
use App\Utilities\Date;
use Illuminate\Support\Facades\Cache;

trait Plans
{
    use SiteApi;

    public function clearPlansCache(): void
    {
        Cache::forget('plans.limits');
    }

    public function getUserLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('user');
    }

    public function getCompanyLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('company');
    }

    public function getInvoiceLimitOfPlan(): object
    {
        return $this->getPlanLimitByType('invoice');
    }

    public function getAnyActionLimitOfPlan(): object
    {
        $user_limit = $this->getUserLimitOfPlan();
        $company_limit = $this->getCompanyLimitOfPlan();
        $invoice_limit = $this->getInvoiceLimitOfPlan();

        if (! $user_limit->action_status) {
            return $user_limit;
        }

        if (! $company_limit->action_status) {
            return $company_limit;
        }

        if (! $invoice_limit->action_status) {
            return $invoice_limit;
        }

        $limit = new \stdClass();
        $limit->action_status = true;
        $limit->view_status = true;
        $limit->message = "Success";

        return $limit;
    }

    public function getPlanLimitByType($type): object
    {
        $limit = new \stdClass();

        $limit->action_status = true;
        $limit->view_status = true;
        $limit->message = "Success";

        return $limit;
    }

    public function getPlanLimits(): bool|object
    {
        $data = new \stdClass();
        $types = ['user', 'company', 'invoice', 'customer'];

        foreach ($types as $type) {
            $data->$type = new \stdClass();
            $data->$type->action_status = true;
            $data->$type->view_status = true;
            $data->$type->message = "Success";
        }

        return $data;
    }
}
