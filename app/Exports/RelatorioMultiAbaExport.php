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
            new \App\Exports\Sheets\ClienteEquipamentosSheet($this->clienteEquipamentos ?? collect()),
            new \App\Exports\Sheets\EstoqueEquipamentosSheet($this->estoque ?? collect()),
            new \App\Exports\Sheets\PlanTemplatesSheet($this->planTemplates ?? collect()),
            new \App\Exports\Sheets\UsersSheet($this->users ?? collect()),
            new \App\Exports\Sheets\DeletionAuditsSheet($this->deletionAudits ?? collect()),
        ];
    }

    // optional setters for new collections
    public function withClienteEquipamentos($cols) { $this->clienteEquipamentos = $cols; return $this; }
    public function withPlanTemplates($cols) { $this->planTemplates = $cols; return $this; }
    public function withUsers($cols) { $this->users = $cols; return $this; }
    public function withDeletionAudits($cols) { $this->deletionAudits = $cols; return $this; }
    public function withEstoque($cols) { $this->estoque = $cols; return $this; }
    
}
