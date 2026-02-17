@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center" style="min-height: 100vh;">
    <div class="relatorio-cobrancas-card" style="background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 32px; width: 100%; max-width: 1400px; min-height: 700px; margin: 40px auto; padding: 56px 48px; overflow-x: auto;">
        <h1 style="color:#f7b500;font-weight:700;font-size:2.1rem;margin-bottom:24px;">Relatórios Gerais</h1>

        <div class="planos-toolbar" style="max-width:1100px;margin:18px auto 8px auto;display:flex;gap:10px;align-items:center;justify-content:space-between;">
            <div style="flex:1;">
                <p class="mb-0" style="color:#333;">Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.</p>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
                <a href="{{ route('relatorios.gerais.download', ['period' => 'diario']) }}" class="btn btn-primary">Baixar Diário</a>
                <a href="{{ route('relatorios.gerais.download', ['period' => 'semanal']) }}" class="btn btn-secondary">Baixar Semanal</a>
                <a href="{{ route('relatorios.gerais.download', ['period' => 'mensal']) }}" class="btn btn-success">Baixar Mensal</a>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped" style="width:100%;min-width:720px;">
                <thead style="background:#f7b500;color:#fff;">
                    <tr>
                        <th>Período</th>
                        <th>Arquivo</th>
                        <th>Data</th>
                        <th style="width:120px;text-align:center;">Download</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historico ?? [] as $item)
                        <tr>
                            <td>{{ ucfirst($item['period']) }}</td>
                            <td style="word-break:break-all;">{{ $item['name'] }}</td>
                            <td>{{ $item['date'] }}</td>
                            <td class="text-center">
                                <a href="{{ $item['url'] }}" class="btn-icon" title="Baixar" aria-label="Baixar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Nenhum relatório disponível.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* toolbar buttons sizing (scoped) */
.planos-toolbar .btn { flex: 0 0 auto; min-width: 120px; padding: 10px 16px; border-radius: 10px; }
.btn-icon { padding:6px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e6e6e6; background:#fff; color:#222; }
.btn-icon:hover { background:#f7b500; color:#fff; border-color:#f7b500; }
@media (max-width: 900px) {
    .planos-toolbar { flex-direction:column; align-items:stretch; }
    .planos-toolbar .btn { width:100%; }
}
</style>
@endsection
