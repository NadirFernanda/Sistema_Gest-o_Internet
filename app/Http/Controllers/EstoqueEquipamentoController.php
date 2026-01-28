<?php

namespace App\Http\Controllers;

use App\Models\EstoqueEquipamento;
use Illuminate\Http\Request;

class EstoqueEquipamentoController extends Controller
{
    public function index()
    {
        $equipamentos = EstoqueEquipamento::all();
        return view('estoque_equipamentos.index', compact('equipamentos'));
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
        ]);
        EstoqueEquipamento::create($request->all());
        return redirect()->route('estoque_equipamentos.index')->with('success', 'Equipamento cadastrado no estoque!');
    }
}
