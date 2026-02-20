<?php

namespace App\Http\Controllers;

use App\Models\EstoqueEquipamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EstoqueEquipamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = EstoqueEquipamento::query();

        if ($busca = trim((string) $request->query('busca', ''))) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'ILIKE', "%{$busca}%")
                    ->orWhere('descricao', 'ILIKE', "%{$busca}%")
                    ->orWhere('modelo', 'ILIKE', "%{$busca}%")
                    ->orWhere('numero_serie', 'ILIKE', "%{$busca}%");
            });
        }

        $equipamentos = $query->orderBy('nome')->get();

        return view('estoque_equipamentos.index', [
            'equipamentos' => $equipamentos,
            'busca' => $busca ?? '',
        ]);
    }

    public function create()
    {
        return view('estoque_equipamentos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'numero_serie' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'imagem' => 'nullable|image|max:2048',
        ]);
        $data = $request->only(['nome','descricao','modelo','numero_serie','quantidade']);
        if ($request->hasFile('imagem')) {
            $data['imagem'] = $request->file('imagem')->store('estoque_equipamentos', 'public');
        }
        EstoqueEquipamento::create($data);
        return redirect()->route('estoque_equipamentos.index')->with('success', 'Equipamento cadastrado no estoque!');
    }

    public function edit(EstoqueEquipamento $equipamento)
    {
        return view('estoque_equipamentos.edit', [
            'equipamento' => $equipamento,
        ]);
    }

    public function update(Request $request, EstoqueEquipamento $equipamento)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'numero_serie' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'imagem' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nome','descricao','modelo','numero_serie','quantidade']);
        if ($request->hasFile('imagem')) {
            // delete old image if present
            if ($equipamento->imagem) {
                Storage::disk('public')->delete($equipamento->imagem);
            }
            $data['imagem'] = $request->file('imagem')->store('estoque_equipamentos', 'public');
        }

        $equipamento->update($data);

        return redirect()->route('estoque_equipamentos.index')->with('success', 'Equipamento atualizado com sucesso!');
    }

    public function destroy(EstoqueEquipamento $equipamento)
    {
        $equipamento->delete();
        return redirect()->route('estoque_equipamentos.index')->with('success', 'Equipamento removido do estoque.');
    }
}
