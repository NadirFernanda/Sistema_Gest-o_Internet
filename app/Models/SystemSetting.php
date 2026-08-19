<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table      = 'system_settings';
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType    = 'string';
    protected $fillable   = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("sys_setting_{$key}", 300, function () use ($key, $default) {
            $row = static::find($key);
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("sys_setting_{$key}");
    }

    public static function mailConfig(): array
    {
        return [
            'host'       => static::get('mail_host', ''),
            'port'       => (int) static::get('mail_port', 587),
            'username'   => static::get('mail_username', ''),
            'password'   => static::get('mail_password', ''),
            'encryption' => static::get('mail_encryption', 'tls'),
            'from_name'  => static::get('mail_from_name', 'AngolaWiFi'),
            'from_email' => static::get('mail_from_email', ''),
        ];
    }
}
