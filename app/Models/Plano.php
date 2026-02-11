<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plano extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'ciclo',
        'template_id',
        'cliente_id',
        'estado',
        'data_ativacao',
    ];

    public function template()
    {
        return $this->belongsTo(PlanTemplate::class, 'template_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
