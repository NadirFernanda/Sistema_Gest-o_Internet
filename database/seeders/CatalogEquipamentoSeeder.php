<?php

namespace Database\Seeders;

use App\Models\CatalogEquipamento;
use Illuminate\Database\Seeder;

class CatalogEquipamentoSeeder extends Seeder
{
    public function run(): void
    {
        $produtos = [
            [
                'nome'       => 'Router TP-Link TL-WR840N',
                'descricao'  => 'Router WiFi 300Mbps, ideal para apartamentos. 2 antenas externas, fácil configuração.',
                'categoria'  => 'Routers',
                'preco'      => 12000,
                'imagem_url' => null,
                'quantidade' => 10,
                'ativo'      => true,
            ],
            [
                'nome'       => 'Router TP-Link Archer C6',
                'descricao'  => 'Router WiFi AC1200 dual-band (2.4GHz + 5GHz). 5 portas Gigabit, 4 antenas.',
                'categoria'  => 'Routers',
                'preco'      => 22000,
                'imagem_url' => null,
                'quantidade' => 8,
                'ativo'      => true,
            ],
            [
                'nome'       => 'Repetidor WiFi TP-Link TL-WA850RE',
                'descricao'  => 'Amplificador de sinal WiFi 300Mbps. Elimina zonas mortas sem fios. Plug & play.',
                'categoria'  => 'Repetidores',
                'preco'      => 8500,
                'imagem_url' => null,
                'quantidade' => 15,
                'ativo'      => true,
            ],
            [
                'nome'       => 'Antena Omnidirecional 9dBi',
                'descricao'  => 'Antena externa 9dBi para ampliar cobertura WiFi. Compatível com a maioria dos routers.',
                'categoria'  => 'Antenas',
                'preco'      => 4500,
                'imagem_url' => null,
                'quantidade' => 20,
                'ativo'      => true,
            ],
            [
                'nome'       => 'Cabo UTP Cat6 — 10 metros',
                'descricao'  => 'Cabo de rede Cat6 com conectores RJ45 prensados. Velocidade até 1Gbps.',
                'categoria'  => 'Cabos',
                'preco'      => 2500,
                'imagem_url' => null,
                'quantidade' => 50,
                'ativo'      => true,
            ],
        ];

        foreach ($produtos as $produto) {
            CatalogEquipamento::firstOrCreate(
                ['nome' => $produto['nome']],
                $produto
            );
        }
    }
}
