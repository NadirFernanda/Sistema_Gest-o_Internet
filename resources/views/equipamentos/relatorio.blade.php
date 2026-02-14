@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/relatorio-cobrancas.css') }}">
<style>
.filtro-modern-cobranca .filtro-group {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 180px;
    max-width: 260px;
    width: 100%;
    flex: 1 1 260px;
    margin-bottom: 18px;
}
.filtro-modern-cobranca .filtro-group input[type="text"] {
    border-radius: 24px;
    border: 1.5px solid #f7b500;
    padding: 10px 40px 10px 16px;
    font-size: 1.08rem;
    box-shadow: 0 2px 8px rgba(247,181,0,0.07);
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    background: #fff;
    width: 100%;
    max-width: 260px;
}
@media (max-width: 700px) {
    .filtro-modern-cobranca .filtro-group {
        max-width: 100%;
    }
    .filtro-modern-cobranca .filtro-group input[type="text"] {
        max-width: 100%;
    }
}
.filtro-modern-cobranca .filtro-group input[type="text"]:focus {
    border-color: #f7b500;
    box-shadow: 0 4px 16px rgba(247,181,0,0.13);
}
.filtro-modern-cobranca .filtro-group .search-icon {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #f7b500;
    font-size: 1.2rem;
    pointer-events: none;
}
.filtro-modern-cobranca .filtro-group input[type="text"]::placeholder {
    color: #b8b8b8;
    font-style: italic;
    opacity: 1;
}
.filtro-modern-cobranca .btn {
    margin-bottom: 0 !important;
}
</style>
<div class="d-flex justify-content-center" style="min-height: 100vh;">
    <div class="relatorio-cobrancas-card" style="background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 32px; width: 100%; max-width: 1400px; min-height: 700px; margin: 40px auto; padding: 56px 48px; overflow-x: auto;">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('dashboard') }}" class="btn-back-circle btn-ghost" title="Voltar ao Dashboard" aria-label="Voltar ao Dashboard">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
    </div>
    <h1 style="color:#f7b500;font-weight:700;font-size:2.1rem;margin-bottom:32px;">Relatório de Equipamentos em Estoque</h1>
    <div class="planos-toolbar" style="max-width:1100px;margin:18px auto 32px auto;display:flex;gap:10px;align-items:center;">
        <form class="search-form-inline" method="GET" action="{{ route('equipamentos.relatorio') }}" style="flex:1;display:flex;gap:8px;align-items:center;">
            <input type="search" name="nome" id="nome" class="search-input" value="{{ request('nome') }}" placeholder="Pesquise por equipamento..." aria-label="Pesquisar equipamentos" style="flex:1;padding:10px 12px;border-radius:6px;border:2px solid #e6a248;" />
            <button type="submit" class="btn btn-search" style="padding:8px 12px;">Pesquisar</button>
        </form>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('equipamentos.relatorio.export', request()->all()) }}" class="btn btn-cta" style="min-width:140px;">Exportar Excel</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
        </div>
    </div>
    <table class="table table-bordered table-striped mt-4" style="width:auto; min-width: 700px; font-size: 1.05rem; margin-bottom:0;">
        <thead style="background:#f7b500;color:#fff;">
            <tr>
                <th>Nº</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Modelo</th>
                <th>Nº Série</th>
                <th>Morada</th>
                <th>Ponto de Referência</th>
                <th>Quantidade em Estoque</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipamentos as $equipamento)
                <tr>
                    <td>{{ $equipamento->id }}</td>
                    <td>{{ $equipamento->nome }}</td>
                    <td>{{ $equipamento->descricao ?? '-' }}</td>
                    <td>{{ $equipamento->modelo ?? '-' }}</td>
                    <td>{{ $equipamento->numero_serie ?? '-' }}</td>
                    <td>{{ $equipamento->morada ?? '-' }}</td>
                    <td>{{ $equipamento->ponto_referencia ?? '-' }}</td>
                    <td>{{ $equipamento->quantidade ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum equipamento encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $equipamentos->links() }}
    </div>
    </div>
</div>
@endsection
