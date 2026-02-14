@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4" style="color:#f7b500;font-weight:bold;">Relatórios Gerais</h1>
    <p class="mb-3">Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.</p>
    <div class="d-flex flex-wrap gap-3 mb-4">
        <a href="{{ route('relatorios.gerais.download', ['period' => 'diario']) }}" class="btn btn-success">Baixar Diário</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'semanal']) }}" class="btn btn-success">Baixar Semanal</a>
        <a href="{{ route('relatorios.gerais.download', ['period' => 'mensal']) }}" class="btn btn-success">Baixar Mensal</a>
    </div>
    <small class="text-muted">Os arquivos são gerados automaticamente pelo sistema e incluem todas as movimentações do período.</small>
    <hr class="my-4">
    <h5 class="mb-2">Histórico de Relatórios Disponíveis</h5>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Período</th>
                <th>Arquivo</th>
                <th>Data</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            @forelse($historico ?? [] as $item)
                <tr>
                    <td>{{ ucfirst($item['period']) }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['date'] }}</td>
                    <td><a href="{{ $item['url'] }}" class="btn btn-sm btn-success">Baixar</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Nenhum relatório disponível.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
