<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteEquipamento extends Model
{
    use HasFactory;

    protected $table = 'cliente_equipamento';

    protected $fillable = [
        'cliente_id',
        'estoque_equipamento_id',
        'morada',
        'ponto_referencia',
        'quantidade',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function equipamento()
    {
        return $this->belongsTo(EstoqueEquipamento::class, 'estoque_equipamento_id');
    }
}
