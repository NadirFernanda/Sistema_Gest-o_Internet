<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cliente extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'mikrotik_site_id',
        'bi',
        'nome',
        'email',
        'contato',
    ];
    public function mikrotikSite()
    {
        return $this->belongsTo(MikroTikSite::class, 'mikrotik_site_id');
    }

    public function planos()
    {
        return $this->hasMany(Plano::class);
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class);
    }

    public function clienteEquipamentos()
    {
        return $this->hasMany(ClienteEquipamento::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    /**
     * Estado calculado a partir dos planos do cliente.
     * Requer que a relação 'planos' esteja carregada (eager load).
     */
    public function getEstadoAttribute($value): string
    {
        if (! $this->relationLoaded('planos')) {
            return $value ?? 'Ativo';
        }

        $planos = $this->planos;

        if ($planos->isEmpty()) {
            return 'Sem plano';
        }
        if ($planos->whereIn('estado', ['Ativo', 'Em aviso'])->isNotEmpty()) {
            return 'Ativo';
        }
        if ($planos->where('estado', 'Suspenso')->isNotEmpty()) {
            return 'Suspenso';
        }
        return 'Inativo';
    }
}
