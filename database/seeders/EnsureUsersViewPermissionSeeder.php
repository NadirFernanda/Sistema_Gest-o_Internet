<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

class EnsureUsersViewPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure permission exists
        Permission::firstOrCreate(['name' => 'users.view']);

        // Ensure role exists and has the permission
        $role = Role::firstOrCreate(['name' => 'Administrador']);
        $role->givePermissionTo('users.view');

        // Reset permission cache
        try {
            Artisan::call('permission:cache-reset');
        } catch (\Throwable $e) {
            // ignore if artisan call not available in some environments
        }
    }
}
