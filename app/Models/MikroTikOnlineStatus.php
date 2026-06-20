<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikroTikOnlineStatus extends Model
{
    use HasFactory;

    protected $table = 'mikrotik_online_statuses';

    protected $fillable = [
        'plano_id',
        'mikrotik_site_id',
        'is_online',
        'last_seen_online_at',
        'last_seen_offline_at',
        'total_downtime_seconds',
        'disconnect_reason',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_online_at' => 'datetime',
        'last_seen_offline_at' => 'datetime',
        'total_downtime_seconds' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function mikrotikSite(): BelongsTo
    {
        return $this->belongsTo(MikroTikSite::class);
    }

    public function events()
    {
        return $this->hasMany(MikroTikOnlineStatusEvent::class, 'mikrotik_online_status_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getDowttimeMinutes(): int
    {
        if (!$this->is_online && $this->last_seen_offline_at) {
            return (int) now()->diffInMinutes($this->last_seen_offline_at);
        }
        return 0;
    }

    public function getDowntimeHours(): float
    {
        if (!$this->is_online && $this->last_seen_offline_at) {
            return now()->diffInHours($this->last_seen_offline_at, true);
        }
        return 0;
    }

    public function getDowntimeDays(): float
    {
        if (!$this->is_online && $this->last_seen_offline_at) {
            return now()->diffInDays($this->last_seen_offline_at, true);
        }
        return 0;
    }

    public function getUpSinceDays(): float
    {
        if ($this->is_online && $this->last_seen_online_at) {
            return now()->diffInDays($this->last_seen_online_at, true);
        }
        return 0;
    }

    public function getUpSinceHours(): float
    {
        if ($this->is_online && $this->last_seen_online_at) {
            return now()->diffInHours($this->last_seen_online_at, true);
        }
        return 0;
    }
}
