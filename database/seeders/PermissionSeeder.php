<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Clientes
            'clientes.view', 'clientes.create', 'clientes.edit', 'clientes.delete',
            // Planos
            'planos.view', 'planos.create', 'planos.edit', 'planos.delete',
            // Usuários
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Cobranças
            'cobrancas.view', 'cobrancas.create', 'cobrancas.edit', 'cobrancas.delete', 'cobrancas.export',
            // Estoque
            'estoque.view', 'estoque.create', 'estoque.edit', 'estoque.delete',
            // Relatórios
            'relatorios.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Assign permissions to roles
        $admin = Role::firstWhere('name', 'Administrador');
        $gestor = Role::firstWhere('name', 'Gestor');
        $colab = Role::firstWhere('name', 'Colaborador');

        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        if ($gestor) {
            $gestorPerms = [
                'clientes.view','clientes.create','clientes.edit',
                'planos.view','planos.create','planos.edit',
                'cobrancas.view','cobrancas.create','cobrancas.edit','cobrancas.export',
                'relatorios.view',
            ];
            $gestor->givePermissionTo($gestorPerms);
        }

        if ($colab) {
            $colabPerms = [
                'clientes.view','clientes.create',
                'planos.view',
                'cobrancas.view','cobrancas.create',
                'estoque.view',
            ];
            $colab->givePermissionTo($colabPerms);
        }
    }
}
