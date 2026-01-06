<?php

namespace Modules\Sunat\Database\Seeds;

use Illuminate\Database\Seeder;
use App\Models\Auth\Permission;
use App\Models\Auth\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            [
                'name' => 'read-sunat-settings',
                'display_name' => 'Read Sunat Settings',
                'description' => 'Read Sunat Settings',
            ],
            [
                'name' => 'update-sunat-settings',
                'display_name' => 'Update Sunat Settings',
                'description' => 'Update Sunat Settings',
            ],
            [
                'name' => 'read-sunat-emissions',
                'display_name' => 'Read Sunat Emissions',
                'description' => 'Read Sunat Emissions',
            ],
        ];

        $adminRole = Role::where('name', 'admin')->first();

        foreach ($permissions as $permission) {
            $p = Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'],
                    'description' => $permission['description'],
                ]
            );

            if ($adminRole && !$adminRole->hasPermission($p->name)) {
                $adminRole->attachPermission($p);
            }
        }
    }
}
