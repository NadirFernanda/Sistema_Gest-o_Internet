<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueEquipamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'descricao',
        'modelo',
        'numero_serie',
        'quantidade',
    ];
}
