@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
    <style>
        /* Page-specific styles */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.48); align-items:center; justify-content:center; z-index:1200; }
        #templatesModal .modal { background:#fff; width:94%; max-width:1200px; border-radius:10px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,0.16); max-height:80vh; overflow:auto; }
        #planosLista .action-buttons{ display:flex; flex-direction:column; gap:8px; align-items:flex-end; }
        #planosLista .action-buttons .btn{ min-width:120px; padding:8px 12px; border-radius:10px; font-weight:700; font-size:0.95rem; }
        #planosLista .action-buttons .btn-remove{ background: #f3f3f3 !important; color: #222 !important; box-shadow: none !important; border: 1px solid #e6e6e6 !important; }
        @media (max-width:900px){ #planosLista .action-buttons{ flex-direction:row; } #planosLista .action-buttons .btn{ min-width:96px; } }
    </style>
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos',
            'subtitle' => 'Lista, gestão e modelos de planos',
            'heroCtAs' => '<a href="' . route('dashboard') . '" class="btn btn-ghost">Dashboard</a><a href="' . route('plan-templates.index') . '" id="manageTemplatesBtn" class="btn btn-cta">Gerir Modelos</a><a href="' . route('planos.create') . '" class="btn btn-cta" id="openCreatePlano">Cadastrar Plano</a>'
        ])

        <div id="planCreateContainer" style="display:none; margin-top:16px;">
            @includeWhen(true, 'planos._form')
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-top:16px;padding:12px;border-radius:6px;background:#e6f7d9;color:#155724;">{{ session('success') }}</div>
        @endif

        <div class="search-row" style="margin-top:32px;">
            <h2 style="margin:0 0 8px 0;">Lista de Planos</h2>
            <div class="search-bar" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div class="search-input-wrap" style="position:relative;flex:1;min-width:220px;max-width:760px;">
                    <input type="text" id="buscaPlanos" class="search-input" placeholder="Pesquisar por plano ou cliente..." aria-label="Pesquisar planos" style="width:100%;padding:12px;border-radius:10px;border:1px solid #e9e9e9;" />
                </div>
                <button type="button" id="btnBuscarPlanos" class="btn-cta search-btn" style="padding:10px 16px;">Pesquisar</button>
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
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
@endpush

@section('content')
    <div class="planos-container">
        <header class="clientes-hero modern-hero">
            <div class="hero-inner">
                <div class="hero-left">
                    <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
                    <div class="hero-titles">
                        <h1>Gestão de Planos</h1>
                        <p class="hero-sub">Lista, gestão e modelos de planos</p>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="hero-ctas">
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                        <a href="{{ route('plan-templates.index') }}" id="manageTemplatesBtn" class="btn btn-cta">Gerir Modelos</a>
                        <button type="button" id="refreshTemplatesBtn" class="btn btn-cta">Atualizar Modelos</button>
                        <a href="{{ route('planos.create') }}" class="btn btn-cta" id="openCreatePlano">Cadastrar Plano</a>
                    </div>
                </div>
            </div>
        </header>

        <div id="planCreateContainer" style="display:none; margin-top:16px;">
            @includeWhen(true, 'planos._form')
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="margin-top:16px;padding:12px;border-radius:6px;background:#e6f7d9;color:#155724;">
                {{ session('success') }}
            </div>
        @endif

        <div class="search-row" style="margin-top:32px;">
            <h2 style="margin:0 0 8px 0;">Lista de Planos</h2>
            <div class="search-bar" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div class="search-input-wrap" style="position:relative;flex:1;min-width:220px;max-width:760px;">
                    <input type="text" id="buscaPlanos" class="search-input" placeholder="Pesquisar por plano ou cliente..." aria-label="Pesquisar planos" style="width:100%;padding:12px;border-radius:10px;border:1px solid #e9e9e9;" />
                </div>
                <button type="button" id="btnBuscarPlanos" class="btn-cta search-btn" style="padding:10px 16px;">Pesquisar</button>
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
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
    <style>
        /* Page-specific styles moved from inline to head to ensure proper cascade */
        /* simple modal styles */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.48); align-items:center; justify-content:center; z-index:1200; }
        /* make modal wider but limit height so content scrolls internally */
        #templatesModal .modal { background:#fff; width:94%; max-width:1200px; border-radius:10px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,0.16); max-height:80vh; overflow:auto; }
        /* keep modal content scrollable when tall */
        @endpush
        #planosLista .action-buttons{ display:flex; flex-direction:column; gap:8px; align-items:flex-end; }
        #planosLista .action-buttons .btn{ min-width:120px; padding:8px 12px; border-radius:10px; font-weight:700; font-size:0.95rem; }
        /* remove button (danger) styled as muted gray in lists - higher specificity to override global .btn styles */
        #planosLista .action-buttons .btn-remove{
            background: #f3f3f3 !important;
            color: #222 !important;
            box-shadow: none !important;
            border: 1px solid #e6e6e6 !important;
        }
        /* Also override other remove-button selectors used elsewhere so the table remove appears gray */
        #planosLista .btn-remover-plano,
        #planosLista .btn-remover,
        #planosLista button.btn-remover-plano,
        #planosLista button.btn-remover {
            background: #f3f3f3 !important;
            color: #222 !important;
            box-shadow: none !important;
            border: 1px solid #e6e6e6 !important;
        }
        @media (max-width:900px){ #planosLista .action-buttons{ flex-direction:row; } #planosLista .action-buttons .btn{ min-width:96px; } }
        /* Sticky header inside modal so controls remain visible while scrolling content */
        #templatesModal .modal-header { position: sticky; top: 0; z-index: 22; background: #fff; padding-bottom:8px; border-bottom:1px solid #f6f6f6; }
    </style>
@endpush

@section('content')
    <div class="planos-container">
        <header class="clientes-hero modern-hero">
            <div class="hero-inner">
                <div class="hero-left">
                    <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
                    <div class="hero-titles">
                        <h1>Gestão de Planos</h1>
                        <p class="hero-sub">Lista, gestão e modelos de planos</p>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="hero-ctas">
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                        <a href="{{ route('plan-templates.index') }}" id="manageTemplatesBtn" class="btn btn-cta">Gerir Modelos</a>
                        <button type="button" id="refreshTemplatesBtn" class="btn btn-cta">Atualizar Modelos</button>
                        <a href="{{ route('planos.create') }}" class="btn btn-cta" id="openCreatePlano">Cadastrar Plano</a>
                    </div>
                </div>
            </div>
        </header>
        <!-- Hidden inline form container (loaded from partial). Shown only when user clicks 'Cadastrar Plano' -->
        <div id="planCreateContainer" style="display:none; margin-top:16px;">
            @includeWhen(true, 'planos._form')
        </div>
        <!-- Flash messages -->
        @if(session('success'))
            <div class="alert alert-success" style="margin-top:16px;padding:12px;border-radius:6px;background:#e6f7d9;color:#155724;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-top:16px;padding:12px;border-radius:6px;background:#f8d7da;color:#721c24;">
                {{ session('error') }}
            </div>
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
            <div class="search-bar" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div class="search-input-wrap" style="position:relative;flex:1;min-width:220px;max-width:760px;">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.7;">
                        <path d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm8.707 2.293-4.387-4.387" stroke="#6b6b6b" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input type="text" id="buscaPlanos" class="search-input" placeholder="Pesquisar por plano ou cliente..." aria-label="Pesquisar planos" style="width:100%;padding:12px 44px 12px 42px;border-radius:10px;border:1px solid #e9e9e9;box-shadow:0 6px 18px rgba(0,0,0,0.04);" />
                    <button id="clearSearch" class="search-clear" title="Limpar pesquisa" aria-label="Limpar pesquisa" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;font-size:18px;color:#9b9b9b;cursor:pointer;">×</button>
                </div>
                <button type="button" id="btnBuscarPlanos" class="btn-cta search-btn" style="padding:10px 16px;">Pesquisar</button>
            </div>
        </div>
        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>
    @push('scripts')
        <script>
            // Config passed from Blade to bundled JS
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
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const isHidden = window.getComputedStyle(container).display === 'none';
                if(isHidden){
                    container.style.display = 'block';
                    try{ if(typeof window.loadTemplates === 'function') window.loadTemplates(); }catch(_){}
                    setTimeout(() => {
                        const first = container.querySelector('input, select, textarea');
                        if(first) try{ first.focus(); first.scrollIntoView({behavior:'smooth', block:'center'}); }catch(_){}
                    }, 60);
                    btn.textContent = 'Fechar formulário';
                } else {
                    container.style.display = 'none';
                    btn.textContent = 'Cadastrar Plano';
                }
            });
        })();
    </script>
    <script>
        // Populate `#clientePlano` with clients from the server (returns JSON when Accept: application/json)
        document.addEventListener('DOMContentLoaded', function(){
            const clienteSelect = document.getElementById('clientePlano');
            if(!clienteSelect) return;
            fetch('/clientes', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(list => {
                    const items = Array.isArray(list) ? list : (list.data || []);
                    items.forEach(c => {
                        try {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = (c.nome || c.name) + (c.bi ? ' — ' + c.bi : '');
                            clienteSelect.appendChild(opt);
                        } catch(_) { /* ignore malformed entries */ }
                    });
                })
                .catch(() => {
                    // silently ignore - leaving the default option if request fails
                });
        });
    </script>
@endsection
