<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cliente extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'bi', // número do bilhete de identidade
        'nome',
        'email',
        'contato',
    ];
    public function planos()
    {
        return $this->hasMany(Plano::class);
    }

    public function equipamentos()
    {
        return $this->hasMany(Equipamento::class);
    }
}
