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

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
