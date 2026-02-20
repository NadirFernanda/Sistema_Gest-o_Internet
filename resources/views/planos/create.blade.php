@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
    <style>
        /* Page-specific styles copied from planos index to match header layout */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.48); align-items:center; justify-content:center; z-index:1200; }
        #templatesModal .modal { background:#fff; width:94%; max-width:1200px; border-radius:10px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,0.16); max-height:80vh; overflow:auto; }
        #planosLista .action-buttons{ display:flex; gap:8px; align-items:center; }
        #planosLista .action-buttons .btn-icon{ min-width:40px; width:40px; height:40px; padding:6px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; }
        #planosLista .action-buttons .btn-icon svg{ width:18px; height:18px; }
        #planosLista .action-buttons .btn{ min-width:0; padding:6px 8px; border-radius:8px; font-weight:700; font-size:0.95rem; }
        #planosLista .action-buttons .btn-remove{ background: #f3f3f3 !important; color: #222 !important; box-shadow: none !important; border: 1px solid #e6e6e6 !important; }
        @media (max-width:900px){ #planosLista .action-buttons{ flex-direction:row; } #planosLista .action-buttons .btn{ min-width:0; } }
        #planosLista .plan-grid { margin-top: 18px; display:flex; gap:18px; flex-wrap:wrap; align-items:stretch; }
        #planosLista .plan-card .plan-meta { display:flex !important; gap:12px !important; align-items:center !important; flex-wrap:nowrap !important; }
        #planosLista .plan-card .plan-meta > .plan-price,
        #planosLista .plan-card .plan-meta > .plan-cycle { display:inline-flex !important; align-items:center !important; line-height:1 !important; margin:0 8px 0 0 !important; padding:0 !important; }
        #planosLista .plan-card .status-badge { display:none !important; visibility:hidden !important; }
    </style>
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos — Cadastrar',
            'subtitle' => ''
        ])

        <div class="planos-toolbar" style="max-width:1100px;margin:18px auto;display:flex;gap:10px;align-items:center;">
            <form class="search-form-inline" method="GET" action="{{ route('planos.index') }}" style="flex:1;display:flex;gap:8px;align-items:center;">
                <input type="search" name="q" id="buscaPlanos" class="search-input" placeholder="Pesquise por plano ou cliente..." aria-label="Pesquisar planos" style="flex:1;padding:10px 12px;border-radius:6px;border:2px solid #e6a248;" />
                <button type="submit" class="btn btn-search" style="padding:8px 12px;">Pesquisar</button>
            </form>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('plan-templates.index') }}" id="manageTemplatesBtn" class="btn btn-cta">Planos</a>
                @if(auth()->user() && auth()->user()->hasRole('Administrador'))
                    <a href="{{ route('planos.create') }}" class="btn btn-cta">Cadastrar</a>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
            </div>
        </div>

        @include('planos._form')
    </div>
@endsection
