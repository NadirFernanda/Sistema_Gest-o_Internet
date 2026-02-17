@extends('layouts.app')

@section('content')
@include('layouts.partials.clientes-hero', ['title' => 'Relatórios Gerais', 'subtitle' => 'Baixe os relatórios automáticos multi-aba (Clientes, Planos, Cobranças, Equipamentos, Alertas) gerados diariamente, semanalmente e mensalmente.'])

{{-- header styles moved to resources/css/app.css to standardize across pages --}}

<div class="d-flex justify-content-center" style="min-height: 100vh;">
    <div class="relatorio-cobrancas-card" style="background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border-radius: 32px; width: 100%; max-width: 1400px; min-height: 700px; margin: 40px auto; padding: 56px 48px; overflow-x: auto;">
        {{-- copied layout exactly from equipamentos/relatorio.blade.php and adapted headers --}}
        <div class="planos-toolbar" style="max-width:1100px;margin:18px auto 32px auto;display:flex;gap:10px;align-items:center;">
            <form class="search-form-inline" method="GET" action="{{ route('relatorios.gerais') }}" style="flex:1;display:flex;gap:8px;align-items:center;">
                <input type="search" name="q" id="buscaRelatorios" class="search-input" placeholder="Pesquise por nome do arquivo..." aria-label="Pesquisar relatórios" value="{{ request('q') }}" style="flex:1;padding:10px 12px;border-radius:6px;border:2px solid #e6a248;" />
                <button type="submit" class="btn btn-search" style="padding:8px 12px;">Pesquisar</button>
            </form>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
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
    </div>
</div>

@endsection
