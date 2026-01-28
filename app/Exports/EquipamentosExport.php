<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EquipamentosExport implements FromView
{
    protected $equipamentos;
    public function __construct($equipamentos)
    {
        $this->equipamentos = $equipamentos;
    }
    public function view(): View
    {
        return view('equipamentos.export', [
            'equipamentos' => $this->equipamentos
        ]);
    }
}
