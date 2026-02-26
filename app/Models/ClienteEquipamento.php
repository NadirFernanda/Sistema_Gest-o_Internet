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
        'forma_ligacao',
        'status',
        'devolucao_solicitada_at',
        'devolucao_prazo',
        'motivo_requisicao',
    ];

    const STATUS_EMPRESTADO = 'emprestado';
    const STATUS_DEVOLUCAO_SOLICITADA = 'devolucao_solicitada';
    const STATUS_DEVOLVIDO = 'devolvido';

    protected $casts = [
        'devolucao_solicitada_at' => 'datetime',
        'devolucao_prazo' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function equipamento()
    {
        return $this->belongsTo(EstoqueEquipamento::class, 'estoque_equipamento_id');
    }

    public function scopeEmprestados($query)
    {
        return $query->where('status', self::STATUS_EMPRESTADO);
    }

    public function scopeComDevolucaoSolicitada($query)
    {
        return $query->where('status', self::STATUS_DEVOLUCAO_SOLICITADA);
    }
}
