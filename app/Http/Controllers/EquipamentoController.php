<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\Cliente;
use Illuminate\Http\Request;

class EquipamentoController extends Controller
{
    public function create($clienteId)
    {
        $cliente = Cliente::with('equipamentos')->findOrFail($clienteId);
        return view('equipamentos.create', compact('cliente'));
    }

    public function store(Request $request, $clienteId)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'morada' => 'required|string|max:255',
            'ponto_referencia' => 'nullable|string|max:255',
        ]);

        Equipamento::create([
            'cliente_id' => $clienteId,
            'nome' => $request->nome,
            'morada' => $request->morada,
            'ponto_referencia' => $request->ponto_referencia,
        ]);

        return redirect()->route('clientes.show', $clienteId)->with('success', 'Equipamento cadastrado com sucesso!');
    }
}
