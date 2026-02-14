<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EquipamentosSheet implements FromView
{
    protected $equipamentos;
    public function __construct($equipamentos) { $this->equipamentos = $equipamentos; }
    public function view(): View {
        return view('relatorios.sheets.equipamentos', ['equipamentos' => $this->equipamentos]);
    }
}
