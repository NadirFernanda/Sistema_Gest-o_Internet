<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikroTikOnlineStatusEvent extends Model
{
    protected $table = 'mikrotik_online_status_events';

    protected $fillable = [
        'plano_id',
        'mikrotik_online_status_id',
        'event_type',
        'occurred_at',
        'duration_seconds',
        'disconnect_reason',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function mikrotikOnlineStatus()
    {
        return $this->belongsTo(MikroTikOnlineStatus::class);
    }

    // Helpers
    public function getDurationMinutes()
    {
        return $this->duration_seconds ? intdiv($this->duration_seconds, 60) : 0;
    }

    public function getDurationHours()
    {
        return $this->duration_seconds ? intdiv($this->duration_seconds, 3600) : 0;
    }

    public function getDurationDays()
    {
        return $this->duration_seconds ? intdiv($this->duration_seconds, 86400) : 0;
    }

    public function getReadableDuration()
    {
        if (!$this->duration_seconds) {
            return 'Em andamento';
        }

        $days = intdiv($this->duration_seconds, 86400);
        $hours = intdiv($this->duration_seconds % 86400, 3600);
        $minutes = intdiv($this->duration_seconds % 3600, 60);

        $parts = [];
        if ($days > 0) $parts[] = "{$days}d";
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";

        return implode(' ', $parts) ?: '< 1 min';
    }
}
