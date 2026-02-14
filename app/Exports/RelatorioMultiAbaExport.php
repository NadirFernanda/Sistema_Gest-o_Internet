<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RelatorioMultiAbaExport implements WithMultipleSheets
{
    protected $cobrancas;
    protected $clientes;
    protected $planos;
    protected $equipamentos;
    protected $alertas;

    public function __construct($cobrancas, $clientes, $planos, $equipamentos, $alertas)
    {
        $this->cobrancas = $cobrancas;
        $this->clientes = $clientes;
        $this->planos = $planos;
        $this->equipamentos = $equipamentos;
        $this->alertas = $alertas;
    }

    public function sheets(): array
    {
        return [
            new \App\Exports\Sheets\CobrancasSheet($this->cobrancas),
            new \App\Exports\Sheets\ClientesSheet($this->clientes),
            new \App\Exports\Sheets\PlanosSheet($this->planos),
            new \App\Exports\Sheets\EquipamentosSheet($this->equipamentos),
            new \App\Exports\Sheets\AlertasSheet($this->alertas),
        ];
    }
}
