@extends('layouts.app')

@section('content')
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
            <input type="search" name="nome" id="buscaEquipamentos" class="search-input" placeholder="Pesquise por equipamento..." aria-label="Pesquisar equipamentos" value="{{ request('nome') }}" style="flex:1;padding:10px 12px;border-radius:6px;border:2px solid #e6a248;" />
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
