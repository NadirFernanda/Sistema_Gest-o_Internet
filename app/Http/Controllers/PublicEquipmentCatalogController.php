<?php

namespace App\Http\Controllers;

use App\Models\CatalogEquipamento;
use Illuminate\Http\Request;

class PublicEquipmentCatalogController extends Controller
{
    /**
     * Public endpoint: returns active equipment items for the loja catalog.
     * No authentication required — read-only public catalog.
     */
    public function index(Request $request)
    {
        $query = CatalogEquipamento::ativo()->orderBy('categoria')->orderBy('nome');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', '%' . $search . '%')
                  ->orWhere('descricao', 'like', '%' . $search . '%')
                  ->orWhere('categoria', 'like', '%' . $search . '%');
            });
        }

        if ($categoria = $request->query('categoria')) {
            $query->where('categoria', $categoria);
        }

        $items = $query->get();

        return response()->json([
            'data' => $items->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'nome'       => $item->nome,
                    'descricao'  => $item->descricao,
                    'categoria'  => $item->categoria,
                    'preco'      => $item->preco,
                    'imagem_url' => $item->imagem_url,
                    'quantidade' => $item->quantidade,
                    'em_stock'   => $item->isInStock(),
                ];
            }),
        ]);
    }
}
