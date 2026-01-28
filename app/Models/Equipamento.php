<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'nome',
        'morada',
        'ponto_referencia',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
