@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        /* Page-specific styles */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.48); align-items:center; justify-content:center; z-index:1200; }
        #templatesModal .modal { background:#fff; width:94%; max-width:1200px; border-radius:10px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,0.16); max-height:80vh; overflow:auto; }
        #planosLista .action-buttons{ display:flex; gap:8px; align-items:center; }
        /* Prefer compact icon-only buttons in action columns */
        #planosLista .action-buttons .btn-icon{ min-width:40px; width:40px; height:40px; padding:6px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; }
        #planosLista .action-buttons .btn-icon svg{ width:18px; height:18px; }
        /* Fallback textual buttons keep reduced footprint */
        #planosLista .action-buttons .btn{ min-width:0; padding:6px 8px; border-radius:8px; font-weight:700; font-size:0.95rem; }
        #planosLista .action-buttons .btn-remove{ background: #f3f3f3 !important; color: #222 !important; box-shadow: none !important; border: 1px solid #e6e6e6 !important; }
        @media (max-width:900px){ #planosLista .action-buttons{ flex-direction:row; } #planosLista .action-buttons .btn{ min-width:0; } }
        /* Add breathing room between the search bar and the plan cards */
        #planosLista .plan-grid { margin-top: 18px; display:flex; gap:18px; flex-wrap:wrap; align-items:stretch; }
          /* Immediate override to force inline alignment of plan meta items
              Uses high-specificity selectors and !important so it takes effect
              even if other CSS files are loaded later. */
          #planosLista .plan-card .plan-meta { display:flex !important; gap:12px !important; align-items:center !important; flex-wrap:nowrap !important; }
          #planosLista .plan-card .plan-meta > .plan-price,
          #planosLista .plan-card .plan-meta > .plan-cycle { display:inline-flex !important; align-items:center !important; line-height:1 !important; margin:0 8px 0 0 !important; padding:0 !important; }
          /* hide any remaining status badges inside cards (status shown in details only) */
          #planosLista .plan-card .status-badge { display:none !important; visibility:hidden !important; }
          /* Card layout for plan cards: ensure client name fits and description truncates */
          #planosLista .plan-card { background:#fff; padding:12px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.04); min-width:200px; max-width:340px; flex:1 1 220px; display:flex; flex-direction:column; justify-content:space-between; }
          #planosLista .plan-card .plan-title { font-weight:800; font-size:1.06rem; color:#222; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
          #planosLista .plan-card .plan-client { color:#666; font-size:0.95rem; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
          #planosLista .plan-card .muted { margin-top:8px; color:#444; flex:1 1 auto; overflow:hidden; max-height:3.6em; display:block; }
          #planosLista .plan-card .plan-actions { margin-top:12px; display:flex; gap:8px; align-items:center; }
    </style>
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos',
            'subtitle' => 'Lista, gestão e modelos de planos'
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

        {{-- Toolbar única (padrão alertas) --}}
        <div class="alertas-toolbar" style="flex-wrap:wrap;gap:10px;">
            <div class="alertas-toolbar-left" style="flex:1;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                <input type="search" id="buscaPlanos" placeholder="Pesquisar cliente ou plano…"
                       class="search-input" style="min-width:200px;flex:1 1 200px;height:40px;padding:8px 16px;border-radius:8px;border:1px solid #dde3ec;font-size:0.97rem;">

                <select id="filtroEstado" style="height:40px;padding:6px 12px;border-radius:8px;border:1px solid #dde3ec;font-size:0.97rem;background:#fff;cursor:pointer;">
                    <option value="">Todos os estados</option>
                    <option value="Ativo">Ativo</option>
                    <option value="Em aviso">Em aviso</option>
                    <option value="Suspenso">Suspenso</option>
                    <option value="Cancelado">Cancelado</option>
                    <option value="Site">Site</option>
                    <option value="Agente Autorizado">Agente Autorizado</option>
                </select>

                <select id="filtroTipo" style="height:40px;padding:6px 12px;border-radius:8px;border:1px solid #dde3ec;font-size:0.97rem;background:#fff;cursor:pointer;">
                    <option value="">Todos os tipos</option>
                    <option value="familiar">Familiar</option>
                    <option value="institucional">Institucional</option>
                    <option value="empresarial">Empresarial</option>
                    <option value="site">Site</option>
                </select>

                <select id="filtroVencimento" style="height:40px;padding:6px 12px;border-radius:8px;border:1px solid #dde3ec;font-size:0.97rem;background:#fff;cursor:pointer;">
                    <option value="">Qualquer vencimento</option>
                    <option value="vencido">Vencidos</option>
                    <option value="hoje">Vence hoje</option>
                    <option value="avencer">A vencer</option>
                    <option value="vigente">Vigentes</option>
                </select>

                <label for="filtroDias" id="labelDias" style="display:none;white-space:nowrap;font-size:0.95rem;">Dias:</label>
                <input type="number" id="filtroDias" value="5" min="1" max="365"
                       style="display:none;width:72px;height:40px;padding:6px 10px;border-radius:8px;border:1px solid #dde3ec;font-size:0.97rem;">

                <button id="btnLimparFiltros" class="btn btn-ghost" style="height:40px;white-space:nowrap;width:auto;flex-shrink:0;align-self:center;">✕ Limpar</button>
            </div>
            <div class="alertas-toolbar-actions">
                <a href="{{ route('plan-templates.index') }}" id="manageTemplatesBtn" class="btn btn-cta">Planos</a>
                @if(auth()->user() && auth()->user()->hasRole('Administrador'))
                    <a href="{{ route('planos.create') }}" class="btn btn-cta">Cadastrar</a>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
            </div>
        </div>


        <!-- Card único: resumo com todos os planos e suas contagens de clientes -->
        <div class="plan-stats-single" style="max-width:1100px;margin:6px auto 0;">
            <div class="plan-stat-card-single" style="background:#fffbe7;padding:16px;border-radius:10px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <strong style="font-size:1.05rem;color:#333;">Resumo de Clientes por Plano</strong>
                    <span style="color:#666;font-size:0.95rem;">Templates: {{ isset($templates) ? $templates->count() : 0 }}</span>
                </div>
                <div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;">
                    @isset($templates)
                        @foreach($templates as $tpl)
                            <div class="tpl-item" data-template-id="{{ $tpl->id }}" data-template-name="{{ $tpl->name }}"
                                 style="background:#fff;padding:10px;border-radius:8px;border:1px solid rgba(231,214,137,0.4);display:flex;flex-direction:column;cursor:pointer;transition:box-shadow 0.15s,border-color 0.15s;"
                                 title="Clique para filtrar por {{ $tpl->name }}">
                                <div class="tpl-item-title" style="font-weight:700;color:#f7b500;">{{ $tpl->name }}</div>
                                <div class="tpl-item-count" style="font-size:1.4rem;font-weight:800;margin-top:6px;">{{ $tpl->clients_count ?? 0 }}</div>
                                <div class="tpl-item-sub" style="font-size:0.9rem;color:#666;">clientes</div>
                            </div>
                        @endforeach
                    @else
                        <div style="color:#666">Nenhum template de plano encontrado.</div>
                    @endisset
                </div>
            </div>
        </div>

        <div id="activePlanFilter" style="display:none;max-width:1100px;margin:10px auto 0;padding:8px 14px;background:#fff8e1;border:1px solid #f7b500;border-radius:8px;align-items:center;gap:10px;">
            <span style="font-size:0.93rem;color:#333;">A mostrar planos de: <strong id="activePlanFilterName"></strong></span>
            <button id="clearPlanFilter" style="margin-left:auto;background:transparent;border:1px solid #aaa;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:0.88rem;color:#555;">✕ Limpar filtro</button>
        </div>

        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>

    <!-- Modal exibido quando o usuário tenta executar ação sem permissão -->
    <div id="noPermModal" style="display:none;position:fixed;inset:0;z-index:1300;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);">
        <div class="modal">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <h3 style="margin:0;font-size:1.05rem;">Acesso negado</h3>
                <button id="noPermClose" style="background:transparent;border:0;font-size:1.1rem;cursor:pointer;">✕</button>
            </div>
            <p id="noPermBody" style="margin:0 0 12px;">Você não tem permissão para executar esta ação. Por favor contacte o administrador do sistema para obter acesso.</p>
            <p style="margin:0 0 18px;">Enviar e-mail: <a id="noPermMailLink" href="mailto:{{ config('mail.from.address', 'admin@angolawifi.ao') }}">{{ config('mail.from.address', 'admin@angolawifi.ao') }}</a></p>
            <div style="text-align:right;"><button id="noPermOk" class="btn btn-ghost">Fechar</button></div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.planosConfig = {
                planTemplatesList: "{{ route('plan-templates.list.json') }}",
                planTemplatesBase: "/plan-templates",
                planosApi: "/api/planos",
                planosCreateRoute: "{{ route('planos.create') }}",
                clientesJson: "/clientes",
                adminContactEmail: "{{ config('mail.from.address', 'admin@angolawifi.ao') }}"
            };
        </script>
    @endpush

@endsection
