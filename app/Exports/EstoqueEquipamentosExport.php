<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EstoqueEquipamentosExport implements FromView
{
    protected $equipamentos;
    public function __construct($equipamentos)
    {
        $this->equipamentos = $equipamentos;
    }
    public function view(): View
    {
        return view('estoque_equipamentos.export', [
            'equipamentos' => $this->equipamentos
        ]);
    }
}
