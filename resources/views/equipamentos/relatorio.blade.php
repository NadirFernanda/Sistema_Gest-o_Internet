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
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Voltar ao Dashboard</a>
    </div>
    <h1 style="color:#f7b500;font-weight:700;font-size:2.1rem;margin-bottom:32px;">Relatório de Equipamentos em Estoque</h1>
    <form method="GET" action="{{ route('equipamentos.relatorio') }}" class="filtro-modern-cobranca" style="margin-bottom:32px;">
        <div class="filtro-group">
            <label for="nome">Nome do Equipamento</label>
            <input type="text" name="nome" id="nome" value="{{ request('nome') }}" placeholder="Pesquisar equipamento...">
            <span class="search-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="#f7b500" stroke-width="2"/><path stroke="#f7b500" stroke-width="2" stroke-linecap="round" d="M20 20l-3.5-3.5"/></svg></span>
        </div>
        <div style="display:flex;gap:16px;align-items:center;">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('equipamentos.relatorio.export', request()->all()) }}" class="btn btn-success" style="min-width:140px;color:#fff;" target="_blank">Exportar Excel</a>
        </div>
    </form>
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
