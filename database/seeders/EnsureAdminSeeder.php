<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EnsureAdminSeeder extends Seeder
{
    public function run()
    {
        $role = Role::firstOrCreate(['name' => 'Administrador']);

        $email = 'admin@angolawifi.ao';
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => 'Administrador',
                'email' => $email,
                'password' => Hash::make('password'),
            ]);
            $this->command->info("Created admin user: {$email} with password 'password'");
        }

        if (! $user->hasRole('Administrador')) {
            $user->assignRole($role);
            $this->command->info("Assigned role 'Administrador' to {$email}");
        }
    }
}
