<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class DeletionAuditsSheet implements FromCollection, WithHeadings, WithTitle
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
    public function title(): string { return 'Auditorias de Exclusão'; }
}
