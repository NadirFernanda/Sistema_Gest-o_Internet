<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikroTikSite extends Model
{
    protected $table = 'mikrotik_sites';

    protected $fillable = [
        'nome', 'localizacao', 'host', 'port',
        'username', 'password', 'user_prefix',
        'default_profile', 'active',
        'api_status', 'api_last_error', 'api_checked_at',
    ];

    protected $casts = [
        'active'         => 'boolean',
        'port'           => 'integer',
        'api_checked_at' => 'datetime',
    ];

    public function updateApiStatus(string $status, ?string $error = null): void
    {
        $this->api_status    = $status;
        $this->api_last_error = $error;
        $this->api_checked_at = now();
        $this->saveQuietly();
    }

    protected $hidden = ['password'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'mikrotik_site_id');
    }

    public function mikrotikOnlineStatuses()
    {
        return $this->hasMany(MikroTikOnlineStatus::class);
    }
}
