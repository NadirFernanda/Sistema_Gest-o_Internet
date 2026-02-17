<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeletionAuditsExport implements FromCollection, WithHeadings
{
    protected $audits;

    public function __construct($audits)
    {
        $this->audits = $audits;
    }

    public function collection()
    {
        return $this->audits->map(function ($a) {
            return [
                'id' => $a->id,
                'entity' => class_basename($a->entity_type),
                'entity_id' => $a->entity_id,
                'user_id' => $a->user_id,
                'reason' => $a->reason,
                'payload' => is_array($a->payload) ? json_encode($a->payload) : $a->payload,
                'created_at' => $a->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Entidade', 'Entity ID', 'Usuário', 'Motivo', 'Payload', 'Quando'];
    }
}
