<?php

namespace App\Http\Controllers;

use App\Models\EstoqueEquipamento;
use Illuminate\Http\Request;
use App\Exports\EquipamentosExport;
use Maatwebsite\Excel\Facades\Excel;

class EquipamentoRelatorioController extends Controller
{
    public function index(Request $request)
    {
        $query = EstoqueEquipamento::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'ilike', '%' . $request->nome . '%');
        }
        // Adicione outros filtros aqui conforme necessário
        $equipamentos = $query->orderBy('nome')->paginate(15)->appends($request->all());
        return view('equipamentos.relatorio', compact('equipamentos'));
    }

    public function exportExcel(Request $request)
    {
        $query = EstoqueEquipamento::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'ilike', '%' . $request->nome . '%');
        }
        // Adicione outros filtros aqui conforme necessário
        $equipamentos = $query->orderBy('nome')->get();
        return Excel::download(new EquipamentosExport($equipamentos), 'relatorio_equipamentos.xlsx');
    }
}
