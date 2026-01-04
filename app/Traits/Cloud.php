<?php

namespace App\Traits;

use App\Traits\Modules;

trait Cloud
{
    use Modules;

    public function getCloudRolesPageUrl($location = 'user')
    {
        if ($this->moduleIsEnabled('roles')) {
            return route('roles.roles.index');
        }

        return '';
    }

    public function getCloudBankFeedsUrl($location = 'widget')
    {
        if ($this->moduleIsEnabled('bank-feeds')) {
            return route('bank-feeds.bank-connections.index');
        }

        return '';
    }

    public function isCloud()
    {
        return request()->getHost() == config('cloud.host', 'app.akaunting.com');
    }
}
