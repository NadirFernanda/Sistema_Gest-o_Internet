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
    ];

    protected $casts = [
        'active' => 'boolean',
        'port'   => 'integer',
    ];

    protected $hidden = ['password'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'mikrotik_site_id');
    }
}
