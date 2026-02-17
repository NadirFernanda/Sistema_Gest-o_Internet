@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center" style="min-height: 100vh;">
    <div class="relatorio-cobrancas-card" style="background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 32px; width: 100%; max-width: 1400px; min-height: 700px; margin: 40px auto; padding: 56px 48px; overflow-x: auto;">
        <h1 style="color:#f7b500;font-weight:700;font-size:2.1rem;margin-bottom:32px;text-align:center;">Relatórios Gerais</h1>
        <p class="mb-3" style="text-align:center;max-width:1100px;margin:0 auto 18px;">Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.</p>

        <div class="planos-toolbar" style="max-width:1100px;margin:18px auto 32px auto;display:flex;gap:10px;align-items:center;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
            <a href="{{ route('relatorios.gerais.download', ['period' => 'diario']) }}" class="btn btn-primary">Baixar Diário</a>
            <a href="{{ route('relatorios.gerais.download', ['period' => 'semanal']) }}" class="btn btn-secondary">Baixar Semanal</a>
            <a href="{{ route('relatorios.gerais.download', ['period' => 'mensal']) }}" class="btn btn-success">Baixar Mensal</a>
        </div>

        <small class="text-muted">Os arquivos são gerados automaticamente pelo sistema e incluem todas as movimentações do período.</small>
        <hr class="my-4">

        <h5 class="mb-2" style="color:#f7b500;font-weight:700;">Histórico de Relatórios Disponíveis</h5>

        <div class="estoque-tabela-moderna" style="margin-top:22px;">
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
                            <td style="text-align:left;vertical-align:middle;word-break:break-all;">{{ $item['name'] }}</td>
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
</div>

<style>
.estoque-tabela-moderna {
    background: transparent;
    border-radius: 12px;
    padding: 4px 0 0 0;
    margin-top: 12px;
    overflow-x: auto;
}
.tabela-estoque-moderna {
    width: 100%;
    min-width: 640px;
    font-size: 1em;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.tabela-estoque-moderna th,
.tabela-estoque-moderna td { padding: 10px 12px; }
.tabela-estoque-moderna th { background: #fffbe7; color: #f7b500; font-weight:700; border-bottom: 1px solid #ffe6a0; }
.tabela-estoque-moderna td { background: #fff; color:#222; }
.btn-icon { padding:6px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e6e6e6; background:#fff; color:#222; }
.btn-icon:hover { background:#f7b500; color:#fff; border-color:#f7b500; }
@media (max-width: 900px) {
    .tabela-estoque-moderna { min-width:520px; font-size:0.95em; }
    @media (max-width:640px) { .tabela-estoque-moderna { min-width:480px; } }
}
</style>
@endsection
