<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class EquipamentosSheet implements FromView, WithTitle
{
    protected $equipamentos;
    public function __construct($equipamentos) { $this->equipamentos = $equipamentos; }
    public function view(): View {
        return view('relatorios.sheets.equipamentos', ['equipamentos' => $this->equipamentos]);
    }

    public function title(): string
    {
        return 'Equipamentos';
    }
}
