<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compensacao extends Model
{
    use HasFactory;

    protected $table = 'compensacoes';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'dias',
        'motivo',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
