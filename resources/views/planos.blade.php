@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
    <style>
        /* Page-specific styles */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.48); align-items:center; justify-content:center; z-index:1200; }
        #templatesModal .modal { background:#fff; width:94%; max-width:1200px; border-radius:10px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,0.16); max-height:80vh; overflow:auto; }
        #planosLista .action-buttons{ display:flex; gap:8px; align-items:center; }
        #planosLista .action-buttons .btn{ min-width:0; padding:6px 8px; border-radius:8px; font-weight:700; font-size:0.95rem; }
        #planosLista .action-buttons .btn-remove{ background: #f3f3f3 !important; color: #222 !important; box-shadow: none !important; border: 1px solid #e6e6e6 !important; }
        @media (max-width:900px){ #planosLista .action-buttons{ flex-direction:row; } #planosLista .action-buttons .btn{ min-width:0; } }
    </style>
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos',
            'subtitle' => 'Lista, gestão e modelos de planos',
            'heroCtAs' => '<a href="' . route('dashboard') . '" class="btn btn-ghost">Dashboard</a> <a href="' . route('plan-templates.index') . '" id="manageTemplatesBtn" class="btn btn-cta">Gerir Modelos</a> <a href="' . route('planos.create') . '" class="btn btn-cta" id="openCreatePlano">Cadastrar Plano</a>'
        ])

        <div id="planCreateContainer" style="display:none; margin-top:16px;">
            @includeWhen(true, 'planos._form')
        </div>

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

        <div class="search-row" style="margin-top:32px;">
            <h2 style="margin:0 0 8px 0;">Lista de Planos</h2>
            <div class="search-inline">
                <form class="search-form-inline" method="GET" action="{{ route('planos.index') }}">
                    <input type="search" name="q" id="buscaPlanos" class="search-input" placeholder="Pesquisar por plano ou cliente..." aria-label="Pesquisar planos" />
                    <button type="submit" class="btn btn-search">Pesquisar</button>
                </form>
            </div>
        </div>

        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>

    @push('scripts')
        <script>
            window.planosConfig = {
                planTemplatesList: "{{ route('plan-templates.list.json') }}",
                planTemplatesBase: "/plan-templates",
                planosApi: "/api/planos",
                planosCreateRoute: "{{ route('planos.create') }}",
                clientesJson: "/clientes"
            };
        </script>
    @endpush

@endsection
