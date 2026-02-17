<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminRoleSeeder extends Seeder
{
    public function run()
    {
        // Create admin role if missing
        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->command->info("Role 'admin' ensured.");
    }
}
