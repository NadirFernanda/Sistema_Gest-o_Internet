@extends('layouts.app')
    </div>
    </div>
    <div class="ficha-equip-table">
    <table class="table table-bordered table-striped mt-4 tabela-estoque-moderna" style="width:auto; min-width: 700px; font-size: 1.05rem; margin-bottom:0;">
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
                <tr>
                    <td colspan="4" class="text-center">Nenhum relatório disponível.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $paginacao ?? '' }}
    </div>
    </div>
    </div>
    </div>
                    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
                    <header class="clientes-hero modern-hero">
                        <div class="hero-inner">
                            <div class="hero-left">
                                <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
                                <div class="hero-titles">
                                    <h1>Relatórios Gerais</h1>
                                    <p class="hero-sub">Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.</p>
                                </div>
                            </div>
                            <div class="hero-right">
                                <!-- space reserved for header right (visual only) -->
                            </div>
                        </div>
                    </header>
</div>
@endsection
