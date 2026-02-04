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
            'email' => 'admin@example.com',
        ], [
            'name' => 'Administrador',
            'password' => bcrypt('admin123'),
        ]);
        $admin->assignRole('Administrador');

        // Create a gestor user
        $gestor = User::firstOrCreate([
            'email' => 'gestor@example.com',
        ], [
            'name' => 'Gestor',
            'password' => bcrypt('gestor123'),
        ]);
        $gestor->assignRole('Gestor');

        // Create a colaborador user
        $colab = User::firstOrCreate([
            'email' => 'colaborador@example.com',
        ], [
            'name' => 'Colaborador',
            'password' => bcrypt('colab123'),
        ]);
        $colab->assignRole('Colaborador');
    }
}
