<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        DB::table('clientes')->insert([
            [
                'nome' => 'Cliente Teste 1',
                'email' => 'cliente1@example.com',
                'contato' => '11999999999',
                // 'data_ativacao' removido para alinhar com a tabela
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nome' => 'Cliente Teste 2',
                'email' => 'cliente2@example.com',
                'contato' => '11888888888',
                // 'data_ativacao' removido para alinhar com a tabela
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
