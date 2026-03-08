<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogEquipamento extends Model
{
    protected $table = 'catalog_equipamentos';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'preco',
        'imagem_url',
        'quantidade',
        'ativo',
    ];

    protected $casts = [
        'ativo'     => 'boolean',
        'preco'     => 'integer',
        'quantidade' => 'integer',
    ];

    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    public function isInStock(): bool
    {
        return $this->quantidade > 0;
    }
}
