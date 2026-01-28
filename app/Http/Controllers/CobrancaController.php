<?php

namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Exports\CobrancasExport;
use Maatwebsite\Excel\Facades\Excel;

class CobrancaController extends Controller
{
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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        $debug = [];
        if ($request->filled('cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('nome', 'ilike', '%' . $request->cliente . '%');
            });
        }
        if ($request->filled('descricao')) {
            $query->where('descricao', 'ilike', '%' . $request->descricao . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        $debug['sql'] = $sql;
        $debug['bindings'] = $bindings;
        $cobrancas = $query->orderByDesc('created_at')->paginate(15)->appends($request->all());
        return view('cobrancas.index', compact('cobrancas', 'debug'));
    }

    public function show($id)
    {
        $cobranca = Cobranca::with('cliente')->findOrFail($id);
        return view('cobrancas.show', compact('cobranca'));
    }
    public function create()
    {
        $clientes = \App\Models\Cliente::orderBy('nome')->get();
        return view('cobrancas.create', compact('clientes'));
    }
}
