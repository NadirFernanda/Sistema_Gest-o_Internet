<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'payload_before' => 'array',
        'payload_after' => 'array',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    // Prevent deletion through Eloquent by default
    public function delete()
    {
        throw new \Exception('Audit logs are immutable and cannot be deleted.');
    }
}
