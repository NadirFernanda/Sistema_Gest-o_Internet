<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CobrancasSheet implements FromView
{
    protected $cobrancas;
    public function __construct($cobrancas) { $this->cobrancas = $cobrancas; }
    public function view(): View {
        return view('relatorios.sheets.cobrancas', ['cobrancas' => $this->cobrancas]);
    }
}
