<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class AlertasSheet implements FromView, WithTitle
{
    protected $alertas;
    public function __construct($alertas) { $this->alertas = $alertas; }
    public function view(): View {
        return view('relatorios.sheets.alertas', ['alertas' => $this->alertas]);
    }

    public function title(): string
    {
        return 'Alertas';
    }
}
