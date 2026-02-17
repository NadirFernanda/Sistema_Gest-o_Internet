<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_id', 'actor_name', 'actor_role', 'ip', 'user_agent', 'module',
        'resource_type', 'resource_id', 'action', 'before', 'after', 'request_id', 'channel', 'hmac', 'prev_hash'
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
