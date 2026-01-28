<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteEquipamento;
use App\Models\EstoqueEquipamento;
use Illuminate\Http\Request;

class ClienteEquipamentoController extends Controller
{
    public function create($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $equipamentos = EstoqueEquipamento::all();
        $vinculados = ClienteEquipamento::where('cliente_id', $clienteId)->with('equipamento')->get();
        return view('cliente_equipamento.create', compact('cliente', 'equipamentos', 'vinculados'));
    }

    public function store(Request $request, $clienteId)
    {
        $request->validate([
            'estoque_equipamento_id' => [
                'required',
                'exists:estoque_equipamentos,id',
                function ($attribute, $value, $fail) use ($clienteId) {
                    if (\App\Models\ClienteEquipamento::where('cliente_id', $clienteId)->where('estoque_equipamento_id', $value)->exists()) {
                        $fail('Este equipamento já foi vinculado a esse cliente');
                    }
                }
            ],
            'quantidade' => 'required|integer|min:1',
            'morada' => 'required|string|max:255',
            'ponto_referencia' => 'required|string|max:255',
        ]);
        ClienteEquipamento::create([
            'cliente_id' => $clienteId,
            'estoque_equipamento_id' => $request->estoque_equipamento_id,
            'quantidade' => $request->quantidade,
            'morada' => $request->morada,
            'ponto_referencia' => $request->ponto_referencia,
        ]);
        return redirect()->route('cliente_equipamento.create', $clienteId)->with('success', 'Equipamento vinculado ao cliente!');
    }

    public function edit($clienteId, $vinculoId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $equipamentos = EstoqueEquipamento::all();
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);
        return view('cliente_equipamento.edit', compact('cliente', 'equipamentos', 'vinculo'));
    }

    public function update(Request $request, $clienteId, $vinculoId)
    {
        $request->validate([
            'estoque_equipamento_id' => [
                'required',
                'exists:estoque_equipamentos,id',
                function ($attribute, $value, $fail) use ($clienteId, $vinculoId) {
                    if (\App\Models\ClienteEquipamento::where('cliente_id', $clienteId)
                        ->where('estoque_equipamento_id', $value)
                        ->where('id', '!=', $vinculoId)
                        ->exists()) {
                        $fail('Este equipamento já está vinculado a este cliente.');
                    }
                }
            ],
            'quantidade' => 'required|integer|min:1',
            'morada' => 'required|string|max:255',
            'ponto_referencia' => 'required|string|max:255',
        ]);
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);
        $vinculo->update([
            'estoque_equipamento_id' => $request->estoque_equipamento_id,
            'quantidade' => $request->quantidade,
            'morada' => $request->morada,
            'ponto_referencia' => $request->ponto_referencia,
        ]);
        return redirect()->route('cliente_equipamento.create', $clienteId)->with('success', 'Vínculo atualizado com sucesso!');
    }

    public function destroy($clienteId, $vinculoId)
    {
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);
        $vinculo->delete();
        return redirect()->route('cliente_equipamento.create', $clienteId)->with('success', 'Vínculo removido com sucesso!');
    }
}
        

