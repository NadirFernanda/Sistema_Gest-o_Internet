<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
// ...existing imports...

use App\Models\Cobranca;
use App\Models\Cliente;
use App\Models\Equipamento;
use Illuminate\Http\Request;
use App\Exports\CobrancasExport;
use Maatwebsite\Excel\Facades\Excel;

class CobrancaController extends Controller
{
    public function comprovante($id)
    {
        $cobranca = Cobranca::with('cliente')->findOrFail($id);
        $pdf = Pdf::loadView('cobrancas.comprovante', compact('cobranca'));
        // Envia o comprovante por email ao cliente, se houver email válido
        if ($cobranca->cliente && filter_var($cobranca->cliente->email, FILTER_VALIDATE_EMAIL)) {
            $cobranca->cliente->notify(new \App\Notifications\ComprovantePagamentoEmail($cobranca));
        }
        return $pdf->download('comprovativo_pagamento_'.$cobranca->id.'.pdf');
    }
    public function exportExcel(Request $request)
    {
        $query = Cobranca::with('cliente');
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nome', 'ilike', '%' . $request->cliente . '%');
            });
        }
        if ($request->filled('descricao')) {
            $query->where('descricao', 'ilike', '%' . $request->descricao . '%');
        }
        // Allow filtering by equipamento: find equipamento's cliente and filter cobrancas for that cliente
        if ($request->filled('equipamento')) {
            $equip = Equipamento::find($request->equipamento);
            if ($equip) {
                $query->where('cliente_id', $equip->cliente_id);
            }
        }
        if ($request->filled('valor')) {
            $query->where('valor', $request->valor);
        }
        $temVenc = $request->filled('data_vencimento');
        $temPag = $request->filled('data_pagamento');
        if ($temVenc && $temPag) {
            $dataVenc = $request->data_vencimento;
            $dataPag = $request->data_pagamento;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataVenc)) {
                $dataVenc = date('Y-m-d', strtotime($dataVenc));
            }
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataPag)) {
                $dataPag = date('Y-m-d', strtotime($dataPag));
            }
            $vencInicio = \Carbon\Carbon::parse($dataVenc)->startOfDay();
            $vencFim = \Carbon\Carbon::parse($dataVenc)->endOfDay();
            $pagInicio = \Carbon\Carbon::parse($dataPag)->startOfDay();
            $pagFim = \Carbon\Carbon::parse($dataPag)->endOfDay();
            $query->where(function($q) use ($vencInicio, $vencFim, $pagInicio, $pagFim) {
                $q->whereBetween('data_vencimento', [$vencInicio, $vencFim])
                  ->orWhereBetween('data_pagamento', [$pagInicio, $pagFim]);
            });
        } elseif ($temVenc) {
            $dataVenc = $request->data_vencimento;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataVenc)) {
                $dataVenc = date('Y-m-d', strtotime($dataVenc));
            }
            $vencInicio = \Carbon\Carbon::parse($dataVenc)->startOfDay();
            $vencFim = \Carbon\Carbon::parse($dataVenc)->endOfDay();
            $query->whereBetween('data_vencimento', [$vencInicio, $vencFim]);
        } elseif ($temPag) {
            $dataPag = $request->data_pagamento;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataPag)) {
                $dataPag = date('Y-m-d', strtotime($dataPag));
            }
            $pagInicio = \Carbon\Carbon::parse($dataPag)->startOfDay();
            $pagFim = \Carbon\Carbon::parse($dataPag)->endOfDay();
            $query->whereBetween('data_pagamento', [$pagInicio, $pagFim]);
        }
        $cobrancas = $query->orderByDesc('created_at')->get();
        return Excel::download(new CobrancasExport($cobrancas), 'relatorio_cobrancas.xlsx');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'status' => 'required|in:pendente,pago,atrasado',
        ]);
        Cobranca::create($validated);
        return redirect()->route('cobrancas.index')->with('success', 'Cobrança cadastrada com sucesso!');
    }
    public function index(Request $request)
    {
        $query = Cobranca::with('cliente');
        // lista de clientes e equipamentos para preencher os filtros
        $clientes = \App\Models\Cliente::orderBy('nome')->select('nome')->get();
        $equipamentos = Equipamento::orderBy('nome')->select('id','nome','cliente_id')->get();
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nome', 'ilike', '%' . $request->cliente . '%');
            });
        }
        if ($request->filled('descricao')) {
            $query->where('descricao', 'ilike', '%' . $request->descricao . '%');
        }
        if ($request->filled('equipamento')) {
            $equip = Equipamento::find($request->equipamento);
            if ($equip) {
                $query->where('cliente_id', $equip->cliente_id);
            }
        }
        if ($request->filled('valor')) {
            $query->where('valor', $request->valor);
        }
        $temVenc = $request->filled('data_vencimento');
        $temPag = $request->filled('data_pagamento');
        if ($temVenc && $temPag) {
            $dataVenc = $request->data_vencimento;
            $dataPag = $request->data_pagamento;
            $debug['data_vencimento_input'] = $dataVenc;
            $debug['data_pagamento_input'] = $dataPag;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataVenc)) {
                $dataVenc = date('Y-m-d', strtotime($dataVenc));
            }
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataPag)) {
                $dataPag = date('Y-m-d', strtotime($dataPag));
            }
            $debug['data_vencimento_normalizado'] = $dataVenc;
            $debug['data_pagamento_normalizado'] = $dataPag;
            $vencInicio = \Carbon\Carbon::parse($dataVenc)->startOfDay();
            $vencFim = \Carbon\Carbon::parse($dataVenc)->endOfDay();
            $pagInicio = \Carbon\Carbon::parse($dataPag)->startOfDay();
            $pagFim = \Carbon\Carbon::parse($dataPag)->endOfDay();
            $debug['data_vencimento_start'] = $vencInicio->toDateTimeString();
            $debug['data_vencimento_end'] = $vencFim->toDateTimeString();
            $debug['data_pagamento_start'] = $pagInicio->toDateTimeString();
            $debug['data_pagamento_end'] = $pagFim->toDateTimeString();
            $query->where(function($q) use ($vencInicio, $vencFim, $pagInicio, $pagFim) {
                $q->whereBetween('data_vencimento', [$vencInicio, $vencFim])
                  ->orWhereBetween('data_pagamento', [$pagInicio, $pagFim]);
            });
        } elseif ($temVenc) {
            $dataVenc = $request->data_vencimento;
            $debug['data_vencimento_input'] = $dataVenc;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataVenc)) {
                $dataVenc = date('Y-m-d', strtotime($dataVenc));
            }
            $debug['data_vencimento_normalizado'] = $dataVenc;
            $vencInicio = \Carbon\Carbon::parse($dataVenc)->startOfDay();
            $vencFim = \Carbon\Carbon::parse($dataVenc)->endOfDay();
            $debug['data_vencimento_start'] = $vencInicio->toDateTimeString();
            $debug['data_vencimento_end'] = $vencFim->toDateTimeString();
            $query->whereBetween('data_vencimento', [$vencInicio, $vencFim]);
        } elseif ($temPag) {
            $dataPag = $request->data_pagamento;
            $debug['data_pagamento_input'] = $dataPag;
            if (preg_match('/\d{2}\/\d{2}\/\d{4}/', $dataPag)) {
                $dataPag = date('Y-m-d', strtotime($dataPag));
            }
            $debug['data_pagamento_normalizado'] = $dataPag;
            $pagInicio = \Carbon\Carbon::parse($dataPag)->startOfDay();
            $pagFim = \Carbon\Carbon::parse($dataPag)->endOfDay();
            $debug['data_pagamento_start'] = $pagInicio->toDateTimeString();
            $debug['data_pagamento_end'] = $pagFim->toDateTimeString();
            $query->whereBetween('data_pagamento', [$pagInicio, $pagFim]);
        }
        $cobrancas = $query->orderByDesc('created_at')->paginate(15)->appends($request->all());
        return view('cobrancas.index', compact('cobrancas', 'clientes', 'equipamentos'));
    }

    public function show($id)
    {
        $cobranca = Cobranca::with('cliente')->findOrFail($id);
        return view('cobrancas.show', compact('cobranca'));
    }
    public function create()
    {
        $clientes = \App\Models\Cliente::orderBy('nome')->select('id','nome')->get();
        return view('cobrancas.create', compact('clientes'));
    }

    public function edit($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        $clientes = Cliente::orderBy('nome')->select('id','nome')->get();
        return view('cobrancas.create', compact('cobranca', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'data_vencimento' => 'required|date',
            'data_pagamento' => 'nullable|date',
            'status' => 'required|in:pendente,pago,atrasado',
        ]);
        $cobranca = Cobranca::findOrFail($id);
        $cobranca->update($validated);
        return redirect()->route('cobrancas.index')->with('success', 'Cobrança atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        $cobranca->delete();
        return redirect()->route('cobrancas.index')->with('success', 'Cobrança removida com sucesso!');
    }
}
