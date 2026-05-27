<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MikroTikExport implements FromView, WithTitle, WithEvents
{
    public function __construct(private $planos, private $sites) {}

    public function view(): View
    {
        return view('relatorios.sheets.mikrotik', [
            'planos' => $this->planos,
            'sites'  => $this->sites,
        ]);
    }

    public function title(): string
    {
        return 'MikroTik';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highest = $sheet->getHighestRow();

                // Col E = Username MikroTik — forçar string para não cortar zeros
                for ($row = 2; $row <= $highest; $row++) {
                    $val = (string) $sheet->getCell('E' . $row)->getValue();
                    $sheet->setCellValueExplicit('E' . $row, $val, DataType::TYPE_STRING);
                }
                $sheet->getStyle('E:E')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                // Larguras das colunas
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(36);
                $sheet->getColumnDimension('C')->setWidth(28);
                $sheet->getColumnDimension('D')->setWidth(28);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(14);
                $sheet->getColumnDimension('G')->setWidth(14);
                $sheet->getColumnDimension('H')->setWidth(18);

                // Estilo do cabeçalho
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F5A623']],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                // Bordas nos dados
                if ($highest > 1) {
                    $sheet->getStyle('A1:H' . $highest)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'E0E0E0']]],
                    ]);
                }

                // Linhas alternadas
                for ($row = 2; $row <= $highest; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F9FAFB']],
                        ]);
                    }
                }

                // Freeze header
                $sheet->freezePane('A2');
            },
        ];
    }
}
