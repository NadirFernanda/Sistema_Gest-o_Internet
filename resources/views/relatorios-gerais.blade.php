@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:900px;margin:40px auto 0 auto;">
    <h1 class="mb-4" style="color:#f7b500;font-weight:bold;">Relatórios Gerais</h1>
    <p class="mb-3">Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.</p>
    <div class="ficha-toolbar mb-4" style="justify-content:center;gap:12px;">
        <a href="{{ route('dashboard') }}" class="ficha-download" style="min-width:140px;text-align:center;">Painel</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'diario']) }}" class="ficha-download" style="min-width:140px;text-align:center;">Baixar Diário</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'semanal']) }}" class="ficha-download" style="min-width:140px;text-align:center;">Baixar Semanal</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'mensal']) }}" class="ficha-download" style="min-width:140px;text-align:center;">Baixar Mensal</a>
    </div>
    <small class="text-muted">Os arquivos são gerados automaticamente pelo sistema e incluem todas as movimentações do período.</small>
    <hr class="my-4">
    <h5 class="mb-2" style="color:#f7b500;font-weight:bold;">Histórico de Relatórios Disponíveis</h5>
    <div class="estoque-tabela-moderna">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;vertical-align:middle;">Período</th>
                    <th style="text-align:center;vertical-align:middle;">Arquivo</th>
                    <th style="text-align:center;vertical-align:middle;">Data</th>
                    <th style="text-align:center;vertical-align:middle;">Download</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historico ?? [] as $item)
                    <tr>
                        <td style="text-align:center;vertical-align:middle;">{{ ucfirst($item['period']) }}</td>
                        <td style="text-align:center;vertical-align:middle;word-break:break-all;">{{ $item['name'] }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $item['date'] }}</td>
                        <td style="white-space:nowrap;text-align:center;vertical-align:middle;">
                            <a href="{{ $item['url'] }}" class="btn-icon" title="Baixar" aria-label="Baixar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center" style="padding:18px 0;">Nenhum relatório disponível.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
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
.tabela-estoque-moderna th,
.tabela-estoque-moderna td {
    padding: 8px 6px;
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
@media (max-width: 900px) {
    .tabela-estoque-moderna {
        min-width: 520px;
        font-size: 0.98em;
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
</style>
@endsection
