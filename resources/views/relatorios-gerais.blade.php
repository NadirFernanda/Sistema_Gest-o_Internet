@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Relatórios Gerais',
        'subtitle' => '',
        'stackLeft' => true,
    ])

    <style>
    /* Reuse toolbar styles from estoque to keep visual parity */
    .clientes-toolbar, .clientes-toolbar form.search-form-inline {
        max-width:1100px;
        margin:18px auto;
        display:flex;
        gap:10px;
        align-items:center;
    }
    .clientes-toolbar form.search-form-inline { flex:1; display:flex; gap:8px; align-items:center; }
    .clientes-toolbar .search-input {
        height:40px !important;
        flex:1 !important;
        min-width:320px !important;
        max-width:100%;
        padding:0 12px !important;
        border-radius:8px !important;
        border:2px solid #e6a248 !important;
        box-sizing:border-box;
        font-size:1rem;
    }
    .clientes-toolbar .btn,
    .clientes-toolbar .btn-search,
    .clientes-toolbar .btn-cta,
    .clientes-toolbar .btn-ghost { height:40px !important; min-width:140px !important; max-width:140px !important; width:140px !important; display:inline-flex; align-items:center; justify-content:center; font-weight:700; border-radius:8px; text-align:center; white-space:nowrap; box-sizing:border-box; }
    .tabela-estoque-moderna th { background: #fffbe7; color: #f7b500; font-weight: bold; font-size: 1.09em; border-bottom: 2px solid #ffe6a0; padding: 14px 12px; }
    .tabela-estoque-moderna td { background: #fff; color: #222; font-size: 1em; padding: 13px 12px; }
    .tabela-estoque-moderna { width:100%; min-width:640px; font-size:1.07em; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; }
    .btn-icon { padding:6px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; border:1px solid #e6e6e6; background:#fff; color:#222; cursor:pointer; transition:background 0.12s ease, color 0.12s ease, border-color 0.12s ease; }
    .btn-icon svg { width:16px; height:16px; }
    .btn-icon:hover { background:#f7b500; color:#fff; border-color:#f7b500; }
    </style>

    <div class="clientes-toolbar">
        <form method="GET" action="{{ route('relatorios.gerais') }}" class="search-form-inline">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Pesquisar por nome do arquivo..." class="search-input" />
            <button type="submit" class="btn btn-search">Pesquisar</button>
            @if(request('q'))
                <a href="{{ route('relatorios.gerais') }}" class="btn btn-ghost" style="margin-left:6px;">Limpar</a>
            @endif
        </form>
        <div style="display:flex;gap:8px;">
            @include('relatorios.buttons')
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
        </div>
    </div>

    <div class="estoque-tabela-moderna" style="margin-top:18px;">
        <table class="tabela-estoque-moderna">
            <thead>
                <tr>
                    <th style="text-align:center;vertical-align:middle;">Período</th>
                    <th style="text-align:center;vertical-align:middle;">Arquivo</th>
                    <th style="text-align:center;vertical-align:middle;">Data</th>
                    <th style="text-align:center;vertical-align:middle;width:120px;">Download</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historico ?? [] as $item)
                    <tr>
                        <td style="text-align:center;vertical-align:middle;">{{ ucfirst($item['period']) }}</td>
                        <td style="word-break:break-all;vertical-align:middle;">{{ $item['name'] }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $item['date'] }}</td>
                        <td style="text-align:center;vertical-align:middle;">
                            <a href="{{ $item['url'] }}" class="btn-icon" title="Baixar" aria-label="Baixar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:18px;">Nenhum relatório disponível.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center" style="margin-top:18px;">
        {{ $paginacao ?? '' }}
    </div>

</div>

@endsection
