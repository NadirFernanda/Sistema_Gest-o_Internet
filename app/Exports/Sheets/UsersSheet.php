<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $rows;
    public function __construct($rows) { $this->rows = $rows; }
    public function collection()
    {
        return $this->rows;
    }
    public function headings(): array
    {
        if ($this->rows->isEmpty()) return ['id'];
        return array_keys($this->rows->first()->toArray());
    }
    public function title(): string { return 'Usuários'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $workbook = $sheet->getParent();
                foreach ($workbook->getAllSheets() as $ws) {
                    $ws->getProtection()->setSheet(true);
                }
            },
        ];
    }
}
