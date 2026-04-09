<?php
namespace App\Exports\Sheets;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                // Force BI (col C) and Contato (col D) as explicit strings to avoid scientific notation
                for ($row = 2; $row <= $highestRow; $row++) {
                    foreach (['C', 'D'] as $col) {
                        $value = (string) $sheet->getCell($col . $row)->getValue();
                        $sheet->setCellValueExplicit($col . $row, $value, DataType::TYPE_STRING);
                    }
                }
                $sheet->getStyle('C:D')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            },
        ];
    }
}
