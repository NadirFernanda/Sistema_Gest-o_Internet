<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'role', 'action', 'auditable_type', 'auditable_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'url'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}
