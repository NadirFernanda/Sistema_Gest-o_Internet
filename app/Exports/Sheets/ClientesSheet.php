<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ClientesSheet implements FromView
{
    protected $clientes;
    public function __construct($clientes) { $this->clientes = $clientes; }
    public function view(): View {
        return view('relatorios.sheets.clientes', ['clientes' => $this->clientes]);
    }
}
