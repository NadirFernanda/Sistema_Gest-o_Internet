@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        /* Page-specific styles (copiado de planos) */
        #sitesLista .action-buttons{ display:flex; gap:8px; align-items:center; }
        #sitesLista .action-buttons .btn-icon{ min-width:40px; width:40px; height:40px; padding:6px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; }
        #sitesLista .action-buttons .btn-icon svg{ width:18px; height:18px; }
        #sitesLista .action-buttons .btn{ min-width:0; padding:6px 8px; border-radius:8px; font-weight:700; font-size:0.95rem; }
        #sitesLista .action-buttons .btn-remove{ background: #f3f3f3 !important; color: #222 !important; box-shadow: none !important; border: 1px solid #e6e6e6 !important; }
        @media (max-width:900px){ #sitesLista .action-buttons{ flex-direction:row; } #sitesLista .action-buttons .btn{ min-width:0; } }
        #sitesLista .site-grid { margin-top: 18px; display:flex; gap:18px; flex-wrap:wrap; align-items:stretch; }
        #sitesLista .site-card { background:#fff; padding:12px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.04); min-width:200px; max-width:340px; flex:1 1 220px; display:flex; flex-direction:column; justify-content:space-between; }
        #sitesLista .site-title { font-weight:800; font-size:1.06rem; color:#222; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        #sitesLista .site-client { color:#666; font-size:0.95rem; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        #sitesLista .muted { margin-top:8px; color:#444; flex:1 1 auto; overflow:hidden; max-height:3.6em; display:block; }
        #sitesLista .site-actions { margin-top:12px; display:flex; gap:8px; align-items:center; }
    </style>
@endpush

@section('content')
    <div class="sites-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Sites',
            'subtitle' => 'Lista e gestão de sites'
        ])

        @if(session('success'))
            <div class="alert alert-success" style="margin-top:16px;padding:12px;border-radius:6px;background:#e6f7d9;color:#155724;">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger" style="margin-top:16px;padding:12px;border-radius:6px;background:#f8d7da;color:#721c24;">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="margin-top:16px;padding:12px;border-radius:6px;background:#f8d7da;color:#721c24;">
                <ul style="margin:0 0 0 18px;padding:0;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="sites-toolbar" style="max-width:1100px;margin:18px auto;display:flex;gap:10px;align-items:center;">
            <form class="search-form-inline" method="GET" action="{{ route('sites.index') }}" style="flex:1;display:flex;gap:8px;align-items:center;">
                <input type="search" name="busca" id="buscaSites" value="{{ request('busca') }}" class="search-input" placeholder="Pesquise por site..." aria-label="Pesquisar sites" style="flex:5;padding:12px 16px;border-radius:8px;border:2px solid #ffc107;min-width:320px;height:48px;box-sizing:border-box;font-size:1.15rem;" />
                <button type="submit" id="btnBuscarSites" class="btn btn-cta" style="min-width:120px;max-width:120px;height:48px;box-sizing:border-box;padding:0 18px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;">Pesquisar</button>
            </form>
            <div style="display:flex;gap:8px;">
                @if(auth()->user() && auth()->user()->hasRole('Administrador'))
                    <a href="{{ route('sites.create') }}" class="btn btn-cta" style="min-width:120px;max-width:120px;height:48px;box-sizing:border-box;padding:0 18px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;">Cadastrar</a>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="min-width:120px;max-width:120px;height:48px;box-sizing:border-box;padding:0 18px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;">Painel</a>
            </div>
        </div>

        <div class="sites-lista" id="sitesLista">
            <p>Nenhum site cadastrado ainda.</p>
        </div>
    </div>
@endsection
