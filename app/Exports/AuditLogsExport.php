<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\AuditLog;

class AuditLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['id','created_at','user_id','user_name','user_email','roles','action','auditable_type','auditable_id','old_values','new_values','ip_address','hmac'];
    }

    public function map($log): array
    {
        $u = $log->user ?? null;
        $roles = '';
        if ($u && $u->relationLoaded('roles')) {
            $roles = $u->roles->pluck('name')->join('|');
        } elseif (isset($log->role)) {
            $roles = $log->role;
        }

        return [
            $log->id,
            $log->created_at ? $log->created_at->toDateTimeString() : '',
            $log->user_id,
            $u ? $u->name : '',
            $u ? $u->email : '',
            $roles,
            $log->action,
            $log->auditable_type,
            $log->auditable_id,
            $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '',
            $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '',
            $log->ip_address,
            $log->hmac ?? '',
        ];
    }
}
