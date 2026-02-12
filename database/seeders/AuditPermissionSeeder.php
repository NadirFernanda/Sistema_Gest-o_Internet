<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuditPermissionSeeder extends Seeder
{
    public function run()
    {
        // create permission if not exists
        Permission::firstOrCreate(['name' => 'audits.view']);

        // assign to admin role if exists
        $role = Role::where('name', 'admin')->first();
        if ($role) {
            $role->givePermissionTo('audits.view');
            $this->command->info("Permission 'audits.view' assigned to role 'admin'.");
        } else {
            $this->command->warn("Role 'admin' not found. Created permission only.");
        }
    }
}
