<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ObservacoesSheet implements FromView, WithTitle
{
    protected $meta;
    public function __construct($meta = []) { $this->meta = $meta; }
    public function view(): View {
        return view('relatorios.sheets.observacoes', ['meta' => $this->meta]);
    }
    public function title(): string { return 'Observações'; }
}
