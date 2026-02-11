<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'preco',
        'ciclo',
        'estado',
        'metadata',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function planos()
    {
        return $this->hasMany(Plano::class, 'template_id');
    }
}
