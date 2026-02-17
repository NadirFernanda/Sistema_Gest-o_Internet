@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
    <link rel="stylesheet" href="{{ asset('css/relatorio-cobrancas.css') }}">
@endpush

<div class="d-flex justify-content-center" style="min-height: 100vh;">
    <div class="relatorio-cobrancas-card" style="background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 32px; width: 100%; max-width: 1400px; min-height: 700px; margin: 40px auto; padding: 56px 48px; overflow-x: auto;">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Relatório de Cobranças',
        'subtitle' => '',
    ])

    <!-- toolbar removed here: buttons will be placed above the filter fields inside the filter box -->

    <style>
    .filtro-modern-cobranca {
        background: #f8f8f8;
        border-radius: 12px;
        padding: 14px 14px 10px 14px;
        margin-bottom: 24px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.04);
        display: flex;
        flex-wrap: wrap;
        gap: 12px 14px;
        align-items: flex-end;
        justify-content: flex-start;
    }
    .filtro-modern-cobranca .filtro-group {
        display: flex;
        flex-direction: column;
        min-width: 120px;
        flex: 1 1 140px;
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
        font-size: 1rem;
        padding: 8px 12px;
        min-width: 88px;
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
        <div class="filtro-top" style="display:flex;align-items:center;width:100%;margin-bottom:12px;gap:12px;flex-wrap:nowrap;">
            <div class="filtro-top-actions" style="display:flex;gap:12px;align-items:center;">
                <button type="button" id="filtrar-btn" class="btn btn-primary filtro-btn">Filtrar</button>
                <a href="{{ route('cobrancas.index') }}" class="btn btn-secondary filtro-btn">Limpar</a>
                
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
                @can('cobrancas.create')
                <a href="{{ route('cobrancas.create') }}" class="btn btn-cta">Cobrança</a>
                @endcan
            </div>
        </div>
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
        <!-- ações de filtro agora ficam na toolbar acima (botão Filtrar submete o formulário via JS) -->
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
                        <a href="{{ route('cobrancas.show', $cobranca->id) }}" class="btn-icon btn-ghost" title="Ver Detalhes" aria-label="Ver Detalhes">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        @can('cobrancas.edit')
                        <a href="{{ route('cobrancas.edit', $cobranca->id) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                        </a>
                        @endcan
                        <a href="{{ route('cobrancas.comprovante', $cobranca->id) }}" class="btn-icon btn-success" title="Comprovante PDF" target="_blank" aria-label="Comprovante PDF">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 13h4"/><path d="M10 17h4"/></svg>
                        </a>
                        @can('cobrancas.delete')
                        <form action="{{ route('cobrancas.destroy', $cobranca->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Tem certeza que deseja remover esta cobrança?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon btn-danger" title="Remover" aria-label="Remover" onclick="return confirm('Tem certeza que deseja remover esta cobrança?');">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                        @endcan
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
<script>
document.addEventListener('DOMContentLoaded', function(){
    const exportBtn = document.getElementById('export-excel');
    if(!exportBtn) return;
    const form = document.querySelector('form.filtro-modern-cobranca');
    const exportBase = exportBtn.getAttribute('href');
    exportBtn.addEventListener('click', function(e){
        e.preventDefault();
        if(!form){
            window.open(exportBase, '_blank');
            return;
        }
        const params = new URLSearchParams(new FormData(form)).toString();
        const url = params ? (exportBase + '?' + params) : exportBase;
        window.open(url, '_blank');
    });

    // Filtrar (botão dentro do formulário): submete o formulário
    const filtrarBtn = document.getElementById('filtrar-btn');
    if(filtrarBtn && form){
        filtrarBtn.addEventListener('click', function(){
            form.submit();
        });
    }
});
</script>
</div>
@endsection
