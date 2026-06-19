<?php

namespace App\Http\Controllers;

/**
 * Helper class for displaying MikroTik online status in views.
 * Encapsulates status data without needing the full Eloquent model.
 */
class MikroTikStatusDisplay
{
    public bool $is_online;
    public ?\Carbon\Carbon $last_seen_online_at;
    public ?\Carbon\Carbon $last_seen_offline_at;
    public int $total_downtime_seconds;

    public function __construct(bool $isOnline, ?\Carbon\Carbon $lastSeenOnlineAt, ?\Carbon\Carbon $lastSeenOfflineAt, int $totalDowntimeSeconds = 0)
    {
        $this->is_online = $isOnline;
        $this->last_seen_online_at = $lastSeenOnlineAt;
        $this->last_seen_offline_at = $lastSeenOfflineAt;
        $this->total_downtime_seconds = $totalDowntimeSeconds;
    }

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
