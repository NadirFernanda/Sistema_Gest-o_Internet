<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PlanoSeeder extends Seeder
{
    public function run()
    {
        $clientes = \DB::table('clientes')->get();
        $clienteId1 = $clientes[0]->id ?? 1;
        $clienteId2 = $clientes[1]->id ?? $clienteId1;
            $now = Carbon::now();

            $rows = [
                [
                    'nome' => 'Mais bala',
                    'descricao' => 'Plano básico',
                    'preco' => 5000,
                    'ciclo' => 30,
                    'cliente_id' => $clienteId1,
                    'ativo' => true,
                    'data_ativacao' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'nome' => 'Turbo',
                    'descricao' => 'Plano intermediário',
                    'preco' => 10000,
                    'ciclo' => 30,
                    'cliente_id' => $clienteId2,
                    'ativo' => true,
                    'data_ativacao' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'nome' => 'Premium',
                    'descricao' => 'Plano avançado',
                    'preco' => 20000,
                    'ciclo' => 30,
                    'cliente_id' => $clienteId1,
                    'ativo' => true,
                    'data_ativacao' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            // Use upsert to avoid duplicate-key failures when running seeders multiple times in production.
            // Unique index on (cliente_id, nome, ativo) will be used to decide updates vs inserts.
            DB::table('planos')->upsert(
                $rows,
                ['cliente_id', 'nome', 'ativo'],
                ['descricao', 'preco', 'ciclo', 'data_ativacao', 'updated_at']
            );
    }
}
