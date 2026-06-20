<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikroTikBandwidthSample extends Model
{
    protected $table = 'mikrotik_bandwidth_samples';

    protected $fillable = [
        'plano_id', 'sampled_at',
        'rx_bytes', 'tx_bytes', 'rx_rate', 'tx_rate',
        'ip_address', 'caller_id', 'uptime_seconds',
        'max_rx_bps', 'max_tx_bps',
    ];

    protected $casts = [
        'sampled_at'     => 'datetime',
        'rx_bytes'       => 'integer',
        'tx_bytes'       => 'integer',
        'rx_rate'        => 'integer',
        'tx_rate'        => 'integer',
        'uptime_seconds' => 'integer',
        'max_rx_bps'     => 'integer',
        'max_tx_bps'     => 'integer',
    ];

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function getFormattedRxRate(): string  { return self::formatRate($this->rx_rate); }
    public function getFormattedTxRate(): string  { return self::formatRate($this->tx_rate); }
    public function getFormattedUptime(): string  { return self::formatSeconds($this->uptime_seconds ?? 0); }

    public static function formatRate(int $bps): string
    {
        if ($bps >= 1_000_000) return round($bps / 1_000_000, 1) . ' Mbps';
        if ($bps >= 1_000)     return round($bps / 1_000, 0) . ' kbps';
        return $bps . ' bps';
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) return round($bytes / 1_073_741_824, 2) . ' GB';
        if ($bytes >= 1_048_576)     return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1_024)         return round($bytes / 1_024, 0) . ' KB';
        return $bytes . ' B';
    }

    public static function formatSeconds(int $s): string
    {
        if ($s <= 0) return '—';
        if ($s >= 86400) return intdiv($s, 86400) . 'd ' . intdiv($s % 86400, 3600) . 'h';
        if ($s >= 3600)  return intdiv($s, 3600) . 'h ' . intdiv($s % 3600, 60) . 'm';
        if ($s >= 60)    return intdiv($s, 60) . 'm ' . ($s % 60) . 's';
        return $s . 's';
    }
}
