<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'descricao',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
    ];
    
    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'valor' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Presentation helpers (use in views/controllers without extra parsing)
    public function getValorFormatadoAttribute()
    {
        return number_format($this->valor ?? 0, 2, ',', '.');
    }

    public function getDataVencimentoFormatadaAttribute()
    {
        return $this->data_vencimento ? $this->data_vencimento->format('d/m/Y') : 'Sem data';
    }

    public function getDataPagamentoFormatadaAttribute()
    {
        return $this->data_pagamento ? $this->data_pagamento->format('d/m/Y') : '-';
    }

    public function getClienteNomeAttribute()
    {
        return $this->cliente->nome ?? '-';
    }
}
