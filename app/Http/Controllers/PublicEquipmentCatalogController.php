<?php

namespace App\Http\Controllers;

use App\Models\EstoqueEquipamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicEquipmentCatalogController extends Controller
{
    /**
     * Public endpoint: returns equipment from estoque for the loja catalog.
     * No authentication required — read-only public catalog.
     * Only items with quantidade > 0 are shown (in-stock or orderable).
     */
    public function index(Request $request)
    {
        $query = EstoqueEquipamento::query()->orderBy('nome')->orderBy('modelo');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'ilike', '%' . $search . '%')
                  ->orWhere('descricao', 'ilike', '%' . $search . '%')
                  ->orWhere('modelo', 'ilike', '%' . $search . '%');
            });
        }

        if ($categoria = $request->query('categoria')) {
            $query->where('nome', $categoria);
        }

        $items = $query->get();

        return response()->json([
            'data' => $items->map(function ($item) {
                $imageUrl = $item->imagem
                    ? url(Storage::url($item->imagem))
                    : null;

                return [
                    'id'         => $item->id,
                    'nome'       => $item->modelo ?: $item->nome,
                    'descricao'  => $item->descricao,
                    'categoria'  => $item->nome,
                    'preco'      => $item->preco,
                    'imagem_url' => $imageUrl,
                    'quantidade' => $item->quantidade,
                    'em_stock'   => $item->quantidade > 0,
                ];
            }),
        ]);
    }
}
