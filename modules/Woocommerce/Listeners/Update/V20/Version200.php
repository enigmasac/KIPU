<?php

namespace Modules\Woocommerce\Listeners\Update\V20;

use App\Abstracts\Listeners\Update as Listener;
use App\Events\Install\UpdateFinished;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;
use App\Traits\Permissions;
use Illuminate\Support\Facades\Artisan;

class Version200 extends Listener
{
    use Permissions;

    const ALIAS = 'woocommerce';

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

        Artisan::call('module:migrate', ['alias' => self::ALIAS, '--force' => true]);

        $this->updatePermissions();
    }

    public function updatePermissions()
    {
        $permissions[] = Permission::firstOrCreate([
            'name' => 'read-woocommerce-settings',
            'display_name' => 'Read WooCommerce Settings',
            'description' => 'Read WooCommerce Settings',
        ]);

        $permissions[] = Permission::firstOrCreate([
            'name' => 'update-woocommerce-settings',
            'display_name' => 'Update WooCommerce Settings',
            'description' => 'Update WooCommerce Settings',
        ]);

        $roles = Role::all()->filter(function ($r) {
            return $r->hasPermission('read-admin-panel');
        });

        foreach ($roles as $role) {

            foreach ($permissions as $permission) {
                if ($role->hasPermission($permission->name)) {
                    continue;
                }

                $role->attachPermission($permission);
            }
        }
    }
}
