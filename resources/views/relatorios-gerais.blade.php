@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:900px;margin:40px auto 0 auto;">
    <h1 class="mb-4" style="color:#f7b500;font-weight:bold;">Relatórios Gerais</h1>
    <p class="mb-3">Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.</p>
    <div class="ficha-toolbar mb-4" style="justify-content:center;gap:12px;">
        <a href="{{ route('dashboard') }}" class="ficha-download" style="min-width:140px;text-align:center;">Dashboard</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'diario']) }}" class="ficha-download" style="min-width:140px;text-align:center;">Baixar Diário</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'semanal']) }}" class="ficha-download" style="min-width:140px;text-align:center;">Baixar Semanal</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'mensal']) }}" class="ficha-download" style="min-width:140px;text-align:center;">Baixar Mensal</a>
    </div>
    <small class="text-muted">Os arquivos são gerados automaticamente pelo sistema e incluem todas as movimentações do período.</small>
    <hr class="my-4">
    <h5 class="mb-2">Histórico de Relatórios Disponíveis</h5>
    <table class="table table-bordered table-striped" style="margin:0 auto;max-width:900px;background:#fffbe7;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.06);">
        <thead style="background:#ffe6a1;">
            <tr style="font-size:1.08rem;">
                <th style="padding:14px 10px;text-align:center;">Período</th>
                <th style="padding:14px 10px;text-align:center;">Arquivo</th>
                <th style="padding:14px 10px;text-align:center;">Data</th>
                <th style="padding:14px 10px;text-align:center;">Download</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historico ?? [] as $item)
                <tr style="font-size:1.04rem;">
                    <td style="padding:12px 8px;text-align:center;vertical-align:middle;">{{ ucfirst($item['period']) }}</td>
                    <td style="padding:12px 8px;text-align:center;vertical-align:middle;word-break:break-all;">{{ $item['name'] }}</td>
                    <td style="padding:12px 8px;text-align:center;vertical-align:middle;">{{ $item['date'] }}</td>
                    <td style="padding:12px 8px;text-align:center;vertical-align:middle;">
                        <a href="{{ $item['url'] }}" class="btn btn-sm btn-success" style="min-width:110px;font-weight:600;font-size:1.05rem;background:#f7b500;border:none;color:#fff;border-radius:10px;">Baixar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center" style="padding:18px 0;">Nenhum relatório disponível.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
