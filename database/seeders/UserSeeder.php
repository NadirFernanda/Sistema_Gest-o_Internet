<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@sgangolawifi.ao',
        ], [
            'name' => 'Administrador',
            'password' => bcrypt('passwor'),
        ]);
        $admin->assignRole('Administrador');

        // Create a gestor user
        $gestor = User::firstOrCreate([
            'email' => 'gesor@angolawifi.ao',
        ], [
            'name' => 'Gestor',
            'password' => bcrypt('password'),
        ]);
        $gestor->assignRole('Gestor');

        // Create a colaborador user
        $colab = User::firstOrCreate([
            'email' => 'colaborador@angolawifi.ao',
        ], [
            'name' => 'Colaborador',
            'password' => bcrypt('password'),
        ]);
        $colab->assignRole('Colaborador');
    }
}
