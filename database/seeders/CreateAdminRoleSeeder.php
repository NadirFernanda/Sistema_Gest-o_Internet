<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminRoleSeeder extends Seeder
{
    public function run()
    {
        // Ensure permission exists
        Permission::firstOrCreate(['name' => 'audits.view']);

        // Create admin role if missing
        $role = Role::firstOrCreate(['name' => 'admin']);

        // Assign permission to admin role
        $role->givePermissionTo('audits.view');

        $this->command->info("Role 'admin' ensured and 'audits.view' assigned.");
    }
}
