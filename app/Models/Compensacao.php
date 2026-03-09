<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compensacao extends Model
{
    // Real table schema: plano_id, user_id, dias_compensados, anterior, novo
    use HasFactory;

    protected $table = 'compensacoes';

    protected $fillable = [
        'plano_id',
        'user_id',
        'dias_compensados',
        'anterior',
        'novo',
    ];

    public function plano()
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
