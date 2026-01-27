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
        DB::table('planos')->insert([
            [
                'nome' => 'Mais bala',
                'descricao' => 'Plano básico',
                'preco' => 5000,
                'ciclo' => 30,
                'cliente_id' => $clienteId1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nome' => 'Turbo',
                'descricao' => 'Plano intermediário',
                'preco' => 10000,
                'ciclo' => 30,
                'cliente_id' => $clienteId2,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nome' => 'Premium',
                'descricao' => 'Plano avançado',
                'preco' => 20000,
                'ciclo' => 30,
                'cliente_id' => $clienteId1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
