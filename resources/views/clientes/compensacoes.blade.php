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
            <a href="{{ route('clientes.compensacoes.export', $cliente->id) ?? '#' }}" class="btn btn-cta" target="_blank">Exportar</a>
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
                        <tr>
                            <td style="text-align:center;vertical-align:middle;">{{ $c->id }}</td>
                            <td style="text-align:left;vertical-align:middle;padding-left:12px;">{{ optional($planoMap->get($c->plano_id))->nome ?? ('Plano #' . $c->plano_id) }}</td>
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
@endsection
