@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
    <link rel="stylesheet" href="{{ asset('css/relatorio-cobrancas.css') }}">
@endpush

<div class="d-flex justify-content-center" style="min-height: 100vh;">
    <div class="relatorio-cobrancas-card" style="background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 32px; width: 100%; max-width: 1400px; min-height: 700px; margin: 40px auto; padding: 56px 48px; overflow-x: auto;">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Relatório de Cobranças',
        'subtitle' => '',
        'heroCtAs' => '<a href="' . route('dashboard') . '" class="btn btn-secondary">Voltar ao Dashboard</a><a href="' . route('cobrancas.create') . '" class="btn btn-primary">Nova Cobrança</a>'
    ])
    <style>
    .filtro-modern-cobranca {
        background: #f8f8f8;
        border-radius: 12px;
        padding: 18px 18px 10px 18px;
        margin-bottom: 32px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.04);
        display: flex;
        flex-wrap: wrap;
        gap: 18px 24px;
        align-items: flex-end;
        justify-content: flex-start;
    }
    .filtro-modern-cobranca .filtro-group {
        display: flex;
        flex-direction: column;
        min-width: 180px;
        flex: 1 1 220px;
    }
    .filtro-modern-cobranca label {
        font-size: 0.97rem;
        color: #222;
        margin-bottom: 2px;
        font-weight: 500;
    }
    .filtro-modern-cobranca input,
    .filtro-modern-cobranca select {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 8px 12px;
        font-size: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
        margin-bottom: 0;
    }
    .filtro-modern-cobranca input:focus,
    .filtro-modern-cobranca select:focus {
        border-color: #f7b500;
        box-shadow: 0 2px 8px rgba(247,181,0,0.10);
    }
    .filtro-modern-cobranca .btn-primary {
        background: #f7b500;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        padding: 10px 0;
        min-width: 140px;
        margin-top: 0;
        box-shadow: 0 2px 8px rgba(247,181,0,0.08);
        transition: background 0.2s;
    }
    .filtro-modern-cobranca .btn-primary:hover {
        background: #e0a800;
    }
    @media (max-width: 700px) {
        .filtro-modern-cobranca {
            flex-direction: column;
            gap: 10px 0;
        }
        .filtro-modern-cobranca .btn-primary {
            width: 100%;
        }
    }
    </style>
    <form method="GET" action="{{ route('cobrancas.index') }}" class="filtro-modern-cobranca filtro-moderna-extra">
        <div class="filtro-group">
            <label for="cliente">Cliente</label>
            <input type="text" name="cliente" id="cliente" value="{{ request('cliente') }}" placeholder="Nome do cliente">
        </div>
        <div class="filtro-group">
            <label for="descricao">Descrição</label>
            <input type="text" name="descricao" id="descricao" value="{{ request('descricao') }}" placeholder="Descrição da cobrança">
        </div>
        <div class="filtro-group">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="">Todos</option>
                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                <option value="atrasado" {{ request('status') == 'atrasado' ? 'selected' : '' }}>Atrasado</option>
            </select>
        </div>
        <div class="filtro-group">
            <label for="valor">Valor</label>
            <input type="number" step="0.01" name="valor" id="valor" value="{{ request('valor') }}" placeholder="Valor exato">
        </div>
        <div class="filtro-group">
            <label for="data_vencimento">Data de Vencimento</label>
            <input type="date" name="data_vencimento" id="data_vencimento" value="{{ request('data_vencimento') }}" placeholder="yyyy-mm-dd">
        </div>
        <div class="filtro-group">
            <label for="data_pagamento">Data de Pagamento</label>
            <input type="date" name="data_pagamento" id="data_pagamento" value="{{ request('data_pagamento') }}" placeholder="yyyy-mm-dd">
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <button type="button" class="btn btn-secondary" style="margin-left:8px;min-width:120px" onclick="window.location='{{ route('cobrancas.index') }}'">Limpar Filtros</button>
        <a href="{{ route('cobrancas.export', request()->all()) }}" class="btn btn-success" style="margin-left:8px;min-width:140px;color:#fff;" target="_blank">Exportar Excel</a>
    </form>
    <div class="tabela-cobrancas-moderna">
    <table class="table table-bordered table-striped mt-4">
    <style>
    .relatorio-cobrancas-card table {
        table-layout: auto;
        width: auto !important;
        min-width: 700px;
        max-width: 100%;
    }
    .relatorio-cobrancas-card table th, .relatorio-cobrancas-card table td {
        white-space: nowrap;
        padding-left: 8px !important;
        padding-right: 8px !important;
        font-size: 0.95rem;
    }
    </style>
        <thead>
            <tr>
                <th>Nº</th>
                <th>Cliente</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Pagamento</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cobrancas as $cobranca)
                <tr>
                    <td>{{ $cobranca->id }}</td>
                    <td>{{ $cobranca->cliente->nome ?? '-' }}</td>
                    <td>{{ $cobranca->descricao }}</td>
                    <td>Kz {{ number_format($cobranca->valor, 2, ',', '.') }}</td>
                    <td>{{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : 'Sem data' }}</td>
                    <td>{{ $cobranca->data_pagamento ?? '-' }}</td>
                    <td>
                        @if($cobranca->status === 'pago')
                            <span class="badge bg-success">Pago</span>
                        @elseif($cobranca->status === 'atrasado')
                            <span class="badge bg-danger">Atrasado</span>
                        @else
                            <span class="badge bg-warning text-dark">Pendente</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('cobrancas.show', $cobranca->id) }}" class="btn btn-sm btn-info" title="Ver Detalhes"><i class="fas fa-eye"></i> Detalhes</a>
                        <a href="{{ route('cobrancas.edit', $cobranca->id) }}" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i> Editar</a>
                        <a href="{{ route('cobrancas.comprovante', $cobranca->id) }}" class="btn btn-sm btn-success" title="Comprovante PDF" target="_blank"><i class="fas fa-file-pdf"></i> Comprovante</a>
                        <form action="{{ route('cobrancas.destroy', $cobranca->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Tem certeza que deseja remover esta cobrança?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Remover"><i class="fas fa-trash"></i> Remover</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Nenhuma cobrança encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center" style="margin-top:18px;">
        {{ $cobrancas->links() }}
    </div>
    </div>
    <!-- Botão Voltar movido para cima -->
    <style>
    .relatorio-cabecalho-moderna {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 18px;
    }
    .relatorio-cabecalho-moderna h1 {
        color: #f7b500;
        font-size: 2.1em;
        font-weight: bold;
        margin-bottom: 0;
    }
    .relatorio-cabecalho-botoes {
        display: flex;
        gap: 18px;
        margin-top: 6px;
    }
    .filtro-moderna-extra {
        margin-bottom: 30px;
        margin-top: 10px;
        box-shadow: 0 2px 8px #0001;
    }
    .tabela-cobrancas-moderna {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px #0001;
        padding: 18px 18px 8px 18px;
        margin-top: 18px;
    }
    .tabela-cobrancas-moderna table {
        width: 100%;
        min-width: 700px;
        font-size: 1.01em;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
    }
    .tabela-cobrancas-moderna th {
        background: #fffbe7;
        color: #e0a800;
        font-weight: bold;
        font-size: 1.07em;
        border-bottom: 2px solid #ffe6a0;
    }
    .tabela-cobrancas-moderna td {
        background: #fff;
        color: #222;
        font-size: 1em;
    }
    .tabela-cobrancas-moderna tr {
        border-bottom: 1px solid #f3e6b0;
    }
    .badge.bg-success {
        /* use project yellow instead of green */
        background: #f7b500 !important;
        color: #fff !important;
        font-weight: 500;
        border-radius: 6px;
        padding: 4px 12px;
        margin: 2px 0;
        display: inline-block;
    }
    .badge.bg-danger {
        background: #e53935 !important;
        color: #fff !important;
        font-weight: 500;
        border-radius: 6px;
        padding: 4px 12px;
        margin: 2px 0;
        display: inline-block;
    }
    .badge.bg-warning {
        background: #f7b500 !important;
        color: #222 !important;
        font-weight: 500;
        border-radius: 6px;
        padding: 4px 12px;
        margin: 2px 0;
        display: inline-block;
    }
    </style>
</div>
</div>
@endsection
