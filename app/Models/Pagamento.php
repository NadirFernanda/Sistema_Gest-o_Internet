<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'cobranca_id',
        'gateway_transaction_id',
        'merchant_transaction_id',
        'valor',
        'moeda',
        'telefone',
        'status',
        'gateway_status',
        'gateway_code',
        'gateway_message',
        'gateway_payload',
        'processado_em',
    ];

    protected $casts = [
        'valor'          => 'decimal:2',
        'gateway_payload' => 'array',
        'processado_em'  => 'datetime',
    ];

    public function cobranca()
    {
        return $this->belongsTo(Cobranca::class);
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isPendente(): bool
    {
        return in_array($this->status, ['pendente', 'processando']);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'aprovado'    => '<span class="badge badge-success">Aprovado</span>',
            'pendente'    => '<span class="badge badge-warning">Pendente</span>',
            'processando' => '<span class="badge badge-info">Processando</span>',
            'recusado'    => '<span class="badge badge-danger">Recusado</span>',
            'expirado'    => '<span class="badge badge-secondary">Expirado</span>',
            default       => '<span class="badge badge-light">Erro</span>',
        };
    }
}
