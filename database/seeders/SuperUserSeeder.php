<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates an administrator user with the `Administrador` role.
     * Email: admin@sgmrtexas.angolawifi.ao
     * Password: ChangeMe123! (please change after first login)
     *
     * @return void
     */
    public function run()
    {
        $email = 'admin@sgmrtexas.angolawifi.ao';

        $user = User::where('email', $email)->first();
        if ($user) {
            $this->command->info("Super user already exists: {$email}");
            return;
        }

        // Generate a strong random password
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}<>?';
        $plainPassword = substr(str_shuffle(str_repeat($chars, 5)), 0, 16);

        $user = User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => Hash::make($plainPassword),
        ]);

        // Create or get the superadmin role and give it all permissions
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $permissions = Permission::all();
        if ($permissions->isNotEmpty()) {
            $role->syncPermissions($permissions);
        }

        $user->assignRole($role);

        $this->command->info("Created super admin: {$email}");
        $this->command->info("Temporary password: {$plainPassword}");
    }
}
