<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikroTikBandwidthSample extends Model
{
    protected $table = 'mikrotik_bandwidth_samples';

    protected $fillable = [
        'plano_id',
        'sampled_at',
        'rx_bytes',
        'tx_bytes',
        'rx_rate',
        'tx_rate',
        'ip_address',
    ];

    protected $casts = [
        'sampled_at' => 'datetime',
        'rx_bytes'   => 'integer',
        'tx_bytes'   => 'integer',
        'rx_rate'    => 'integer',
        'tx_rate'    => 'integer',
    ];

    public function plano()
    {
        return $this->belongsTo(Plano::class);
    }

    public function getRxRateMbps(): float
    {
        return round($this->rx_rate / 1_000_000, 2);
    }

    public function getTxRateMbps(): float
    {
        return round($this->tx_rate / 1_000_000, 2);
    }

    public function getFormattedRxRate(): string
    {
        return self::formatRate($this->rx_rate);
    }

    public function getFormattedTxRate(): string
    {
        return self::formatRate($this->tx_rate);
    }

    public static function formatRate(int $bps): string
    {
        if ($bps >= 1_000_000) return round($bps / 1_000_000, 1) . ' Mbps';
        if ($bps >= 1_000)     return round($bps / 1_000, 0) . ' kbps';
        return $bps . ' bps';
    }
}
