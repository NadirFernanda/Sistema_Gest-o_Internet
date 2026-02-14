<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AlertasSheet implements FromView
{
    protected $alertas;
    public function __construct($alertas) { $this->alertas = $alertas; }
    public function view(): View {
        return view('relatorios.sheets.alertas', ['alertas' => $this->alertas]);
    }
}
