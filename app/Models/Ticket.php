<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'cliente_id',
        'user_id',
        'assunto',
        'categoria',
        'prioridade',
        'estado',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function mensagens()
    {
        return $this->hasMany(TicketMensagem::class)->orderBy('created_at');
    }

    public function ultimaMensagem()
    {
        return $this->hasOne(TicketMensagem::class)->latestOfMany();
    }

    public function isAberto(): bool
    {
        return in_array($this->estado, ['Aberto', 'Em Andamento']);
    }

    public static function estadoCor(string $estado): string
    {
        return match ($estado) {
            'Aberto'       => '#3b82f6',
            'Em Andamento' => '#f59e0b',
            'Resolvido'    => '#22c55e',
            'Fechado'      => '#9ca3af',
            default        => '#9ca3af',
        };
    }

    public static function prioridadeCor(string $prioridade): string
    {
        return match ($prioridade) {
            'Baixa'   => '#9ca3af',
            'Normal'  => '#3b82f6',
            'Alta'    => '#f59e0b',
            'Urgente' => '#ef4444',
            default   => '#9ca3af',
        };
    }
}
