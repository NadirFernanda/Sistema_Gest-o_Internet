@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Histórico de Compensações — ' . $cliente->nome,
        'subtitle' => '',
        'stackLeft' => true,
    ])
    {{-- Toolbar padronizada com Planos: pesquisa à esquerda, CTAs à direita --}}
    <style>
    /* Força padronização visual da toolbar de estoque */
    .clientes-toolbar, .clientes-toolbar form.search-form-inline {
        max-width:1100px;
        margin:18px auto;
        display:flex;
        gap:10px;
        align-items:center;
    }
    .clientes-toolbar form.search-form-inline {
        flex:1;
        display:flex;
        gap:8px;
        align-items:center;
    }
    .clientes-toolbar .search-input {
        height:40px !important;
        flex:1 !important;
        min-width:320px !important;
        max-width:100%;
        padding:0 12px !important;
        border-radius:8px !important;
        border:2px solid #e6a248 !important;
        box-sizing:border-box;
        font-size:1rem;
        display:inline-flex;
        align-items:center;
    }
    .clientes-toolbar .btn,
    .clientes-toolbar .btn-search,
    .clientes-toolbar .btn-cta,
    .clientes-toolbar .btn-ghost {
        height:40px !important;
        min-width:140px !important;
        max-width:140px !important;
        width:140px !important;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        border-radius:8px;
        text-align:center;
        white-space:nowrap;
        box-sizing:border-box;
    }
    .clientes-toolbar .btn,
    .clientes-toolbar .btn-search,
    .clientes-toolbar .btn-cta,
    .clientes-toolbar .btn-ghost {
        height:40px !important;
        min-width:140px !important;
        max-width:140px !important;
        width:140px !important;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-weight:700;
        border-radius:8px;
        text-align:center;
        white-space:nowrap;
        box-sizing:border-box;
    }
    </style>

    <div class="clientes-toolbar">
        <form method="GET" action="{{ route('clientes.compensacoes', $cliente->id) }}" class="search-form-inline">
            <input type="search" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar por plano, usuário ou data..." class="search-input" />
            <button type="submit" class="btn btn-search">Pesquisar</button>
            @if(request('busca'))
                <a href="{{ route('clientes.compensacoes', $cliente->id) }}" class="btn btn-ghost" style="margin-left:6px;">Limpar</a>
            @endif
        </form>
        <div style="display:flex;gap:8px;">
            @if(Route::has('clientes.compensacoes.export'))
                <a href="{{ route('clientes.compensacoes.export', $cliente->id) }}" class="btn btn-cta" target="_blank">Exportar</a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
        </div>
    </div>

    @if($compensacoes->isEmpty())
        <div class="alert alert-info">Nenhuma compensação encontrada para este cliente.</div>
    @else
        <div class="estoque-tabela-moderna">
            <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
                <thead>
                    <tr>
                        <th style="text-align:center;vertical-align:middle;width:6%;">ID</th>
                        <th style="text-align:center;vertical-align:middle;width:32%;">Plano</th>
                        <th style="text-align:center;vertical-align:middle;width:8%;">Dias</th>
                        <th style="text-align:center;vertical-align:middle;width:18%;">Anterior</th>
                        <th style="text-align:center;vertical-align:middle;width:18%;">Novo</th>
                        <th style="text-align:center;vertical-align:middle;width:10%;">Usuário</th>
                        <th style="text-align:center;vertical-align:middle;width:8%;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compensacoes as $c)
                        @php
                            // Try both string and int keys (DB may return plano_id as string)
                            $planoObj = $planoMap->get((string)$c->plano_id) ?? $planoMap->get((int)$c->plano_id);
                            $planoNome = trim(optional($planoObj)->nome ?? '');
                            if (!$planoNome) {
                                $planoNome = 'Plano #' . $c->plano_id;
                            }
                        @endphp
                        <tr>
                            <td style="text-align:center;vertical-align:middle;">{{ $c->id }}</td>
                            <td style="text-align:center;vertical-align:middle;">{{ $planoNome }}</td>
                            <td style="text-align:center;vertical-align:middle;">{{ $c->dias_compensados }}</td>
                            <td style="text-align:center;vertical-align:middle;">{{ $c->anterior }}</td>
                            <td style="text-align:center;vertical-align:middle;">{{ $c->novo }}</td>
                            <td style="text-align:center;vertical-align:middle;">{{ optional($users->get($c->user_id))->name ?? ($c->user_id ? 'Usuário #' . $c->user_id : '-') }}</td>
                            <td style="text-align:center;vertical-align:middle;">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
<style>
.estoque-container-moderna {
    max-width: 1100px;
    margin: 48px auto 0 auto;
    background: #fafafa;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    padding: 38px 38px 38px 38px;
    min-width: 350px;
}
.estoque-cabecalho-moderna {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
}
.estoque-cabecalho-moderna h1 {
    color: #111;
    font-size: 2.2em;
    font-weight: bold;
    margin-bottom: 0;
}
.estoque-cabecalho-botoes {
    max-width: 1100px;
    margin: 18px auto 0;
    margin-bottom: 12px;
    padding: 0 12px;
}
.estoque-cabecalho-botoes-inner {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
}
.estoque-cabecalho-botoes-inner .btn-block {
    display: block;
    width: 100%;
    padding: 12px 18px;
    border-radius: 12px;
    background: #f7b500;
    color: #fff;
    text-align: center;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(247,181,0,0.18);
    text-decoration: none;
}
.estoque-cabecalho-botoes-inner .btn-block:hover { opacity: 0.95; }
.estoque-busca-form {
    margin: 0 0 8px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: flex-start;
}
.estoque-busca-form input[type="text"] {
    flex: 1;
    min-width: 220px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
.estoque-busca-form button[type="submit"] {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #f7b500;
    color: #fff;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-busca-form .btn-limpar-busca {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #aaa;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-tabela-moderna {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px #0001;
    padding: 18px 18px 8px 18px;
    margin-top: 18px;
    overflow-x: auto;
}
.tabela-estoque-moderna {
    width: 100%;
    min-width: 640px;
    font-size: 1.07em;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}
.tabela-estoque-moderna td .btn,
.tabela-estoque-moderna td form .btn {
    padding: 6px 8px;
    font-size: 0.95em;
}
.tabela-estoque-moderna th,
.tabela-estoque-moderna td {
    padding: 8px 6px;
}

/* Responsive: hide Nº Série (4th column) on narrower viewports and tighten table */
@media (max-width: 900px) {
    .tabela-estoque-moderna {
        min-width: 520px;
        font-size: 0.98em;
    }
    .tabela-estoque-moderna th:nth-child(4),
    .tabela-estoque-moderna td:nth-child(4) {
        display: none;
    }
    /* Reduce padding on very small screens */
    @media (max-width: 640px) {
        .tabela-estoque-moderna th,
        .tabela-estoque-moderna td {
            padding: 6px 6px;
            font-size: 0.95em;
        }
        .tabela-estoque-moderna { min-width: 480px; }
    }
}
.tabela-estoque-moderna th {
    background: #fffbe7;
    color: #f7b500;
    font-weight: bold;
    font-size: 1.09em;
    border-bottom: 2px solid #ffe6a0;
    padding: 14px 12px;
}
.tabela-estoque-moderna td {
    background: #fff;
    color: #222;
    font-size: 1em;
    padding: 13px 12px;
}
.tabela-estoque-moderna tr {
    border-bottom: 1px solid #f3e6b0;
}
/* Icon buttons for compact actions */
.btn-icon {
    padding: 6px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: 1px solid #e6e6e6;
    background: #fff;
    color: #222;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease, border-color 0.12s ease;
}
.btn-icon svg { width: 16px; height: 16px; }
.btn-icon:hover { background: #f7b500; color: #fff; border-color: #f7b500; }
.btn-icon.btn-danger:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }
</style>
@endsection
