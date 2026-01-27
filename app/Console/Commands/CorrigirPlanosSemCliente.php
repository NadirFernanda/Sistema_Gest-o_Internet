<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Plano;
use App\Models\Cliente;

class CorrigirPlanosSemCliente extends Command
{
    protected $signature = 'corrigir:planos-sem-cliente';
    protected $description = 'Associa planos antigos sem cliente_id a um cliente existente (por nome do plano ou manual)';

    public function handle()
    {
        $planosSemCliente = Plano::whereNull('cliente_id')->get();
        if ($planosSemCliente->isEmpty()) {
            $this->info('Nenhum plano sem cliente_id encontrado.');
            return 0;
        }
        $this->info('Planos sem cliente_id encontrados: ' . $planosSemCliente->count());
        foreach ($planosSemCliente as $plano) {
            // Tenta associar pelo nome do plano ao cliente (ajuste conforme sua lógica)
            $cliente = Cliente::where('plano', $plano->nome)->first();
            if (!$cliente) {
                // Se não encontrar, pega o primeiro cliente cadastrado
                $cliente = Cliente::first();
            }
            if ($cliente) {
                $plano->cliente_id = $cliente->id;
                $plano->save();
                $this->info("Plano '{$plano->nome}' associado ao cliente '{$cliente->nome}' (ID: {$cliente->id})");
            } else {
                $this->warn("Plano '{$plano->nome}' não pôde ser associado a nenhum cliente.");
            }
        }
        $this->info('Correção concluída.');
        return 0;
    }
}
