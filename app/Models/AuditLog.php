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

    // Attribute fallbacks for legacy/variant schemas and nicer display
    public function getActorNameAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        $userId = $this->actor_id ?? $this->user_id ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                return $user->name ?? $user->email ?? "User #{$userId}";
            }
            return "User #{$userId}";
        }

        return null;
    }

    public function getActorRoleAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        // try common legacy fields
        $role = $this->role ?? $this->actor_role ?? null;
        if (!empty($role)) {
            return $role;
        }

        // try to infer from user relation (spatie roles if present)
        $userId = $this->actor_id ?? $this->user_id ?? null;
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user && method_exists($user, 'getRoleNames')) {
                $names = $user->getRoleNames();
                return $names->first() ?? null;
            }
        }

        return null;
    }

    public function getResourceTypeAttribute($value)
    {
        return $value ?? $this->resource_type ?? $this->auditable_type ?? null;
    }

    public function getResourceIdAttribute($value)
    {
        return $value ?? $this->resource_id ?? $this->auditable_id ?? null;
    }
    // Prevent deletion through Eloquent by default
    public function delete()
    {
        throw new \Exception('Audit logs are immutable and cannot be deleted.');
    }
}
