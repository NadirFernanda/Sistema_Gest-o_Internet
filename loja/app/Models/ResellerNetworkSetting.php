<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ResellerNetworkSetting extends Model
{
    protected $table = 'reseller_network_settings';

    protected $fillable = ['key', 'label', 'description', 'value', 'unit'];

    protected $casts = ['value' => 'float'];

    public static function get(string $key, float $default = 0): float
    {
        return Cache::remember("rsetting_{$key}", 300, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? (float) $row->value : $default;
        });
    }

    public static function set(string $key, float $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget("rsetting_{$key}");
    }
}
