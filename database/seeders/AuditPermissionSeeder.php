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

        // try common admin role names (Portuguese and English)
        $role = Role::whereIn('name', ['Administrador', 'administrator', 'admin', 'Admin'])->first();
        if ($role) {
            $role->givePermissionTo('audits.view');
            $this->command->info("Permission 'audits.view' assigned to role '{$role->name}'.");
        } else {
            $this->command->warn("No admin role found (checked 'Administrador', 'administrator', 'admin'). Created permission only.");
        }
    }
}
