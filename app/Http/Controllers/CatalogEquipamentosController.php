<?php

namespace App\Http\Controllers;

use App\Models\CatalogEquipamento;
use Illuminate\Http\Request;

class CatalogEquipamentosController extends Controller
{
    public function index(Request $request)
    {
        $query = CatalogEquipamento::orderBy('categoria')->orderBy('nome');

        if ($busca = trim((string) $request->query('busca', ''))) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('categoria', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%");
            });
        }

        $itens = $query->get();

        return view('catalog_equipamentos.index', compact('itens', 'busca'));
    }

    public function create()
    {
        return view('catalog_equipamentos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:255',
            'descricao'  => 'nullable|string|max:500',
            'categoria'  => 'nullable|string|max:100',
            'preco'      => 'required|integer|min:0',
            'imagem_url' => 'nullable|url|max:500',
            'quantidade' => 'required|integer|min:0',
            'ativo'      => 'boolean',
        ]);

        $data['ativo'] = $request->boolean('ativo', true);

        CatalogEquipamento::create($data);

        return redirect()->route('catalog_equipamentos.index')
            ->with('success', 'Produto adicionado ao catálogo com sucesso.');
    }

    public function edit(CatalogEquipamento $catalog_equipamento)
    {
        return view('catalog_equipamentos.edit', ['item' => $catalog_equipamento]);
    }

    public function update(Request $request, CatalogEquipamento $catalog_equipamento)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:255',
            'descricao'  => 'nullable|string|max:500',
            'categoria'  => 'nullable|string|max:100',
            'preco'      => 'required|integer|min:0',
            'imagem_url' => 'nullable|url|max:500',
            'quantidade' => 'required|integer|min:0',
            'ativo'      => 'boolean',
        ]);

        $data['ativo'] = $request->boolean('ativo');

        $catalog_equipamento->update($data);

        return redirect()->route('catalog_equipamentos.index')
            ->with('success', 'Produto actualizado com sucesso.');
    }

    public function destroy(CatalogEquipamento $catalog_equipamento)
    {
        $catalog_equipamento->delete();

        return redirect()->route('catalog_equipamentos.index')
            ->with('success', 'Produto removido do catálogo.');
    }
}
