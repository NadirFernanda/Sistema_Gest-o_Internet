<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PlanosSheet implements FromView
{
    protected $planos;
    public function __construct($planos) { $this->planos = $planos; }
    public function view(): View {
        return view('relatorios.sheets.planos', ['planos' => $this->planos]);
    }
}
