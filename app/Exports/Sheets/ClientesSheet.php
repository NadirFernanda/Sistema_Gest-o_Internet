<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClientesSheet implements FromView, WithTitle, WithEvents
{
    protected $clientes;
    public function __construct($clientes) { $this->clientes = $clientes; }
    public function view(): View {
        return view('relatorios.sheets.clientes', ['clientes' => $this->clientes]);
    }

    public function title(): string
    {
        return 'Clientes';
    }

    public static function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Force BI (col C) and Contato (col D) to be treated as text in Excel
                $sheet = $event->sheet->getDelegate();
                // apply text format to full columns C and D
                $sheet->getStyle('C:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
