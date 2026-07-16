<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MikroTikOnlineStatus;

class Plano extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome',
        'localizacao',
        'descricao',
        'tipo',
        'preco',
        'ciclo',
        'template_id',
        'cliente_id',
        'estado',
        'data_ativacao',
        'proxima_renovacao',
        'mikrotik_username',
        'mikrotik_synced_at',
    ];

    protected $casts = [
        'ativo'              => 'boolean',
        'data_ativacao'      => 'date',
        'proxima_renovacao'  => 'date',
        'mikrotik_synced_at' => 'datetime',
        'tipo'               => 'string',
    ];

    public function template()
    {
        return $this->belongsTo(PlanTemplate::class, 'template_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mikrotikOnlineStatus()
    {
        return $this->hasOne(MikroTikOnlineStatus::class);
    }
}
