<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class PlanosSheet implements FromView, WithTitle
{
    protected $planos;
    public function __construct($planos) { $this->planos = $planos; }
    public function view(): View {
        return view('relatorios.sheets.planos', ['planos' => $this->planos]);
    }

    public function title(): string
    {
        return 'Planos';
    }
}
