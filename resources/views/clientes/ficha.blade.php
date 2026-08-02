@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
<style>
/* ── Ficha do Cliente — redesign ─────────────────────────────── */
.ficha-wrap {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 16px 56px;
}

/* Flash messages */
.ficha-flash {
    border-radius: 10px;
    padding: 12px 18px;
    margin-bottom: 20px;
    font-size: .92rem;
    font-weight: 500;
}
.ficha-flash--ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.ficha-flash--err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }

/* ── Header card ─────────────────────────────────────────────── */
.ficha-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 18px;
    padding: 28px 32px 24px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 24px;
    color: #fff;
}
.ficha-hero__logo {
    width: 72px;
    height: 72px;
    object-fit: contain;
    border-radius: 12px;
    background: #fff;
    padding: 6px;
    flex-shrink: 0;
}
.ficha-hero__text { flex: 1; }
.ficha-hero__title {
    font-size: 1.55rem;
    font-weight: 800;
    margin: 0 0 4px;
    letter-spacing: -.01em;
}
.ficha-hero__sub {
    font-size: .85rem;
    opacity: .72;
    margin: 0;
}
.ficha-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: .82rem;
    font-weight: 700;
    margin-top: 10px;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.3);
}
.ficha-hero__badge--active { background: rgba(40,167,69,.25); border-color: rgba(40,167,69,.5); }
.ficha-hero__badge--suspended { background: rgba(220,53,69,.25); border-color: rgba(220,53,69,.5); }
.ficha-hero__date {
    font-size: .78rem;
    opacity: .65;
    white-space: nowrap;
    text-align: right;
    flex-shrink: 0;
}

/* ── Section cards ───────────────────────────────────────────── */
.ficha-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 14px rgba(0,0,0,.06);
    margin-bottom: 18px;
    overflow: hidden;
}
.ficha-section__head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 22px;
    border-bottom: 1.5px solid #f0f2f5;
    font-size: .82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #555;
}
.ficha-section__head-icon {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    background: #f7b500;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.ficha-section__body { padding: 20px 22px; }
.ficha-section__body--p0 { padding: 0; }

/* ── Client data grid ────────────────────────────────────────── */
.ficha-data-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 32px;
}
@media (max-width: 560px) { .ficha-data-grid { grid-template-columns: 1fr; } }

.ficha-data-item__label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #aaa;
    font-weight: 700;
    margin-bottom: 3px;
}
.ficha-data-item__value {
    font-size: .98rem;
    font-weight: 700;
    color: #1a1a2e;
}
.ficha-data-item__value--muted { color: #bbb; font-weight: 400; font-style: italic; }

.ficha-observacoes {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f0f2f5;
    font-size: .9rem;
    color: #444;
    line-height: 1.6;
}
.ficha-observacoes__label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #aaa;
    font-weight: 700;
    margin-bottom: 6px;
}

/* ── Estado badge ────────────────────────────────────────────── */
.estado-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 700;
}
.estado-badge--ativo     { background: #d4edda; color: #155724; }
.estado-badge--suspenso  { background: #f8d7da; color: #721c24; }
.estado-badge--aviso     { background: #fff3cd; color: #856404; }
.estado-badge--default   { background: #e9ecef; color: #495057; }

/* ── Empty state ─────────────────────────────────────────────── */
.ficha-empty {
    padding: 28px 22px;
    text-align: center;
    color: #bbb;
    font-size: .9rem;
}

/* ── Charges table ───────────────────────────────────────────── */
.ficha-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .88rem;
}
.ficha-table th {
    padding: 10px 16px;
    text-align: left;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #aaa;
    border-bottom: 2px solid #f0f2f5;
    background: #fafbfd;
}
.ficha-table td {
    padding: 11px 16px;
    border-bottom: 1px solid #f5f6f8;
    color: #333;
    vertical-align: middle;
}
.ficha-table tbody tr:last-child td { border-bottom: none; }
.ficha-table tbody tr:hover td { background: #fafbfd; }

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 700;
}
.badge-status--pago     { background: #d4edda; color: #155724; }
.badge-status--atrasado { background: #f8d7da; color: #721c24; }
.badge-status--pendente { background: #fff3cd; color: #856404; }

/* ── Plan card (compact) ─────────────────────────────────────── */
.ficha-plan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 14px;
}
.ficha-plan-card {
    background: #f8f9fc;
    border: 1.5px solid #eef0f5;
    border-radius: 12px;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: box-shadow .15s, border-color .15s, transform .15s;
}
.ficha-plan-card:hover {
    box-shadow: 0 6px 20px rgba(247,181,0,.14);
    border-color: #f7b500;
    transform: translateY(-3px);
}
.ficha-plan-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}
.ficha-plan-card__name { font-weight: 700; font-size: .98rem; color: #1a1a2e; }
.ficha-plan-card__price { font-weight: 800; font-size: 1.05rem; color: #f7b500; white-space: nowrap; }
.ficha-plan-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: .78rem;
    color: #888;
}
.ficha-plan-card__meta-pill {
    background: #fff;
    border: 1px solid #eef0f5;
    border-radius: 6px;
    padding: 2px 8px;
    font-size: .75rem;
}
.ficha-plan-card__progress { margin-top: 2px; }
.ficha-plan-card__bar {
    height: 5px;
    background: #eef0f5;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 4px;
}
.ficha-plan-card__bar-fill {
    height: 100%;
    border-radius: 3px;
    background: linear-gradient(90deg, #f7b500, #f59e0b);
}
.ficha-plan-card__progress-text { font-size: .72rem; color: #aaa; }
.ficha-plan-card__actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: 2px;
}
.ficha-plan-btn {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    border-radius: 7px;
    font-size: .78rem;
    font-weight: 600;
    border: 1.5px solid #eef0f5;
    background: #fff;
    color: #555;
    text-decoration: none;
    cursor: pointer;
    transition: all .13s;
}
.ficha-plan-btn:hover { border-color: #f7b500; color: #f7b500; background: #fffbe7; }
.ficha-plan-btn--primary { background: #f7b500; color: #fff; border-color: #f7b500; }
.ficha-plan-btn--primary:hover { background: #e5a800; color: #fff; border-color: #e5a800; }
.ficha-plan-comp {
    padding: 6px 10px;
    background: #fffbe7;
    border: 1px solid #ffe6a0;
    border-radius: 8px;
    font-size: .78rem;
    color: #7a6000;
}

/* ── Equipment table ─────────────────────────────────────────── */
.ficha-equip-wrap { overflow-x: auto; }

/* ── Back link ───────────────────────────────────────────────── */
.ficha-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #888;
    font-size: .88rem;
    text-decoration: none;
    margin-bottom: 14px;
}
.ficha-back:hover { color: #f7b500; }

/* ── Modal ───────────────────────────────────────────────────── */
.ficha-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.45);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.ficha-modal-overlay.active { display: flex; }
.ficha-modal {
    background: #fff;
    border-radius: 14px;
    padding: 24px 28px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 16px 48px rgba(0,0,0,.18);
}
.ficha-modal__title { font-weight: 700; font-size: 1rem; margin-bottom: 14px; }
.ficha-modal__footer { display: flex; gap: 8px; justify-content: flex-end; margin-top: 14px; }
.ficha-modal label { font-size: .82rem; color: #666; display: block; margin-bottom: 6px; }
.ficha-modal input[type=number] {
    width: 100%; padding: 9px 12px;
    border: 1.5px solid #e0e6f0; border-radius: 8px;
    font-size: .95rem; margin-bottom: 4px;
}
.ficha-modal input[type=number]:focus { outline: none; border-color: #f7b500; }
</style>
@endpush

@section('content')
<div class="ficha-wrap">

    <a href="{{ route('clientes.show', $cliente->id) }}" class="ficha-back">← Voltar ao Cliente</a>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="ficha-flash ficha-flash--ok">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ficha-flash ficha-flash--err">✗ {{ session('error') }}</div>
    @endif

    {{-- ── Hero ── --}}
    <div class="ficha-hero">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logo" class="ficha-hero__logo">
        <div class="ficha-hero__text">
            <h1 class="ficha-hero__title">{{ $cliente->nome }}</h1>
            <p class="ficha-hero__sub">Ficha do Cliente · {{ $cliente->mikrotikSite?->nome ?? '—' }}</p>
            @php
                $estadoClasse = match(strtolower($cliente->estado ?? '')) {
                    'ativo', 'ativa'     => 'active',
                    'suspenso','suspensa' => 'suspended',
                    default              => '',
                };
            @endphp
            <span class="ficha-hero__badge {{ $estadoClasse ? 'ficha-hero__badge--'.$estadoClasse : '' }}">
                {{ $cliente->estado ?? 'Sem estado' }}
            </span>
        </div>
        <div class="ficha-hero__date">
            Emitido em<br><strong>{{ now()->format('d/m/Y') }}</strong>
        </div>
    </div>

    {{-- ── Dados do Cliente ── --}}
    <div class="ficha-section">
        <div class="ficha-section__head">
            <span class="ficha-section__head-icon">👤</span>
            Dados do Cliente
        </div>
        <div class="ficha-section__body">
            <div class="ficha-data-grid">
                <div>
                    <div class="ficha-data-item__label">Nome / Razão social</div>
                    <div class="ficha-data-item__value">{{ $cliente->nome }}</div>
                </div>
                <div>
                    <div class="ficha-data-item__label">BI / NIF</div>
                    <div class="ficha-data-item__value {{ $cliente->bi ? '' : 'ficha-data-item__value--muted' }}">
                        {{ $cliente->bi ?: 'Não registado' }}
                    </div>
                </div>
                <div>
                    <div class="ficha-data-item__label">Contacto (WhatsApp)</div>
                    <div class="ficha-data-item__value {{ $cliente->contato ? '' : 'ficha-data-item__value--muted' }}">
                        {{ $cliente->contato ?: '—' }}
                    </div>
                </div>
                <div>
                    <div class="ficha-data-item__label">Email</div>
                    <div class="ficha-data-item__value {{ $cliente->email ? '' : 'ficha-data-item__value--muted' }}">
                        {{ $cliente->email ?: '—' }}
                    </div>
                </div>
                <div>
                    <div class="ficha-data-item__label">Estado</div>
                    <div>
                        @php
                            $eBadge = match(strtolower($cliente->estado ?? '')) {
                                'ativo','ativa'       => 'ativo',
                                'suspenso','suspensa' => 'suspenso',
                                'em aviso'            => 'aviso',
                                default               => 'default',
                            };
                        @endphp
                        <span class="estado-badge estado-badge--{{ $eBadge }}">{{ $cliente->estado ?? '—' }}</span>
                    </div>
                </div>
                <div>
                    <div class="ficha-data-item__label">Site MikroTik</div>
                    <div class="ficha-data-item__value {{ $cliente->mikrotikSite ? '' : 'ficha-data-item__value--muted' }}">
                        {{ $cliente->mikrotikSite?->nome ?? '—' }}
                    </div>
                </div>
            </div>

            @if(!empty($cliente->observacoes))
            <div class="ficha-observacoes">
                <div class="ficha-observacoes__label">Observações</div>
                {!! nl2br(e($cliente->observacoes)) !!}
            </div>
            @endif
        </div>
    </div>

    {{-- ── Planos Contratados ── --}}
    <div class="ficha-section">
        <div class="ficha-section__head">
            <span class="ficha-section__head-icon">📋</span>
            Planos Contratados
        </div>
        <div class="ficha-section__body">
            @if(isset($cliente->planos) && $cliente->planos->count())
                <div class="ficha-plan-grid">
                    @foreach($cliente->planos as $pl)
                        @php
                            try {
                                $dataAtiv = !empty($pl->data_ativacao) ? \Carbon\Carbon::parse($pl->data_ativacao)->startOfDay() : null;
                                $dataTerm = null;
                                if (!empty($pl->proxima_renovacao)) {
                                    $dataTerm = \Carbon\Carbon::parse($pl->proxima_renovacao)->startOfDay();
                                } elseif ($dataAtiv && $pl->ciclo) {
                                    $cicloInt = max(1, (int) preg_replace('/[^0-9]/', '', (string)$pl->ciclo));
                                    $dataTerm = $dataAtiv->copy()->addDays($cicloInt - 1)->startOfDay();
                                }
                            } catch (\Exception $e) { $dataAtiv = null; $dataTerm = null; }
                            $hoje     = \Carbon\Carbon::today();
                            $preco    = isset($pl->preco) ? number_format($pl->preco, 2, ',', '.') . ' Kz' : '—';
                            $diasRest = $dataTerm ? $hoje->diffInDays($dataTerm, false) : null;
                            $percent  = 0;
                            if ($dataAtiv && $dataTerm) {
                                $totalC  = max(1, $dataAtiv->diffInDays($dataTerm) + 1);
                                $passed  = max(0, min($totalC, $hoje->diffInDays($dataAtiv)));
                                $percent = (int) floor(($passed / $totalC) * 100);
                                $percent = max(0, min(100, $percent));
                            }
                            $ultimaComp = \App\Models\Compensacao::where('plano_id', $pl->id)->orderByDesc('created_at')->first();
                            $estadoPl   = $pl->estado ?? '—';
                            $ePlBadge   = match(strtolower($estadoPl)) {
                                'ativo','ativa'       => 'ativo',
                                'suspenso','suspensa' => 'suspenso',
                                'em aviso'            => 'aviso',
                                default               => 'default',
                            };
                        @endphp
                        <div class="ficha-plan-card">
                            <div class="ficha-plan-card__header">
                                <div>
                                    <div class="ficha-plan-card__name">{{ $pl->nome ?? 'Plano #' . $pl->id }}</div>
                                    <span class="estado-badge estado-badge--{{ $ePlBadge }}" style="margin-top:5px;font-size:.72rem;">{{ $estadoPl }}</span>
                                </div>
                                <div class="ficha-plan-card__price">{{ $preco }}</div>
                            </div>

                            <div class="ficha-plan-card__meta">
                                <span class="ficha-plan-card__meta-pill">Ciclo: {{ $pl->ciclo ?? '—' }}</span>
                                @if($dataAtiv)
                                <span class="ficha-plan-card__meta-pill">Início: {{ $dataAtiv->format('d/m/Y') }}</span>
                                @endif
                                @if($dataTerm)
                                <span class="ficha-plan-card__meta-pill">
                                    {{ $diasRest >= 0 ? 'Renova: ' : 'Vencido há: ' }}
                                    {{ $dataTerm->format('d/m/Y') }}
                                </span>
                                @endif
                            </div>

                            @if($dataAtiv && $dataTerm)
                            <div class="ficha-plan-card__progress">
                                <div class="ficha-plan-card__bar">
                                    <div class="ficha-plan-card__bar-fill" style="width:{{ $percent }}%"></div>
                                </div>
                                <div class="ficha-plan-card__progress-text">
                                    {{ $percent }}% do ciclo
                                    @if(!is_null($diasRest))
                                        · {{ $diasRest >= 0 ? $diasRest . ' dias restantes' : abs($diasRest) . ' dias vencido' }}
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($ultimaComp)
                            <div class="ficha-plan-comp">
                                ⏱ Última compensação: <strong>+{{ $ultimaComp->dias_compensados }} dias</strong>
                                em {{ \Carbon\Carbon::parse($ultimaComp->created_at)->format('d/m/Y') }}
                                @if($ultimaComp->novo)
                                    → <strong>{{ \Carbon\Carbon::parse($ultimaComp->novo)->format('d/m/Y') }}</strong>
                                @endif
                                &nbsp;·&nbsp;<a href="{{ route('clientes.compensacoes', $cliente->id) }}" style="color:#b07d00;">histórico</a>
                            </div>
                            @endif

                            <div class="ficha-plan-card__actions">
                                <a href="{{ route('clientes.show', $cliente->id) }}?plano={{ $pl->id }}" class="ficha-plan-btn">Ver detalhes</a>
                                <button onclick="document.getElementById('modal-janela-{{ $pl->id }}').classList.add('active')" class="ficha-plan-btn ficha-plan-btn--primary">+ Janela</button>
                                <button onclick="document.getElementById('modal-comp-{{ $pl->id }}').classList.add('active')" class="ficha-plan-btn">Compensar</button>
                                <a href="{{ route('clientes.compensacoes', $cliente->id) }}" class="ficha-plan-btn">📋</a>
                            </div>
                        </div>

                        {{-- Modal Janela --}}
                        <div id="modal-janela-{{ $pl->id }}" class="ficha-modal-overlay" onclick="if(event.target===this)this.classList.remove('active')">
                            <div class="ficha-modal">
                                <div class="ficha-modal__title">Adicionar Janela — {{ $pl->nome ?? 'Plano' }}</div>
                                <form method="POST" action="{{ route('clientes.adicionar_janela', $cliente->id) }}">
                                    @csrf
                                    <input type="hidden" name="plano_id" value="{{ $pl->id }}">
                                    <label>Dias a adicionar <span style="color:#bbb;font-size:.85em;">(padrão: {{ (int) preg_replace('/[^0-9]/', '', $pl->ciclo ?? '30') }} dias)</span></label>
                                    <input name="dias" type="number" min="1" max="365" placeholder="{{ (int) preg_replace('/[^0-9]/', '', $pl->ciclo ?? '30') }}">
                                    <div class="ficha-modal__footer">
                                        <button type="button" onclick="document.getElementById('modal-janela-{{ $pl->id }}').classList.remove('active')" class="ficha-plan-btn">Cancelar</button>
                                        <button type="submit" class="ficha-plan-btn ficha-plan-btn--primary">Confirmar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Modal Compensar --}}
                        <div id="modal-comp-{{ $pl->id }}" class="ficha-modal-overlay" onclick="if(event.target===this)this.classList.remove('active')">
                            <div class="ficha-modal">
                                <div class="ficha-modal__title">Compensar Dias — {{ $pl->nome ?? 'Plano' }}</div>
                                <form method="POST" action="{{ route('clientes.compensar_dias', $cliente->id) }}">
                                    @csrf
                                    <input type="hidden" name="plano_id" value="{{ $pl->id }}">
                                    <label>Dias a compensar (queda / indisponibilidade)</label>
                                    <input name="dias_compensados" type="number" min="1" max="365" required>
                                    <div class="ficha-modal__footer">
                                        <button type="button" onclick="document.getElementById('modal-comp-{{ $pl->id }}').classList.remove('active')" class="ficha-plan-btn">Cancelar</button>
                                        <button type="submit" class="ficha-plan-btn ficha-plan-btn--primary">Confirmar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ficha-empty">Nenhum plano contratado.</div>
            @endif
        </div>
    </div>

    {{-- ── Equipamentos ── --}}
    <div class="ficha-section">
        <div class="ficha-section__head">
            <span class="ficha-section__head-icon">🖥️</span>
            Equipamentos Associados
        </div>
        @if(isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count())
            <div class="ficha-equip-wrap">
                <table class="ficha-table">
                    <thead>
                        <tr>
                            <th>Marca / Nome</th>
                            <th>Descrição</th>
                            <th>Modelo</th>
                            <th>Nº Série</th>
                            <th style="text-align:center">Qtd</th>
                            <th style="text-align:center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->clienteEquipamentos as $vinc)
                            @php $est = $vinc->equipamento; @endphp
                            <tr>
                                <td><strong>{{ $est->nome ?? '—' }}</strong></td>
                                <td>{{ $est->descricao ?? '—' }}</td>
                                <td>{{ $est->modelo ?? '—' }}</td>
                                <td><code style="font-size:.8rem;background:#f4f6f9;padding:2px 6px;border-radius:4px;">{{ $est->numero_serie ?? '—' }}</code></td>
                                <td style="text-align:center">{{ $vinc->quantidade ?? '1' }}</td>
                                <td style="text-align:center;white-space:nowrap;">
                                    <a href="{{ route('cliente_equipamento.edit', [$cliente->id, $vinc->id]) }}" class="btn-icon btn-warning" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    </a>
                                    <form action="{{ route('cliente_equipamento.destroy', [$cliente->id, $vinc->id]) }}" method="POST" style="display:inline-block;margin-left:4px;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-icon btn-danger" title="Apagar" onclick="return confirm('Desvincular este equipamento?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ficha-empty">Nenhum equipamento cadastrado para este cliente.</div>
        @endif
    </div>

    {{-- ── Cobranças ── --}}
    <div class="ficha-section">
        <div class="ficha-section__head">
            <span class="ficha-section__head-icon">💳</span>
            Cobranças (pendentes / recentes)
        </div>
        @if($cliente->cobrancas && $cliente->cobrancas->count())
            <div class="ficha-section__body--p0">
                <table class="ficha-table">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->cobrancas as $c)
                        <tr>
                            <td><span style="color:#bbb;font-size:.82rem;">#</span>{{ $c->id }}</td>
                            <td>{{ $c->descricao ?? '—' }}</td>
                            <td><strong>{{ number_format($c->valor, 2, ',', '.') }} Kz</strong></td>
                            <td>{{ $c->data_vencimento ? \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') : '—' }}</td>
                            <td>
                                @if(($c->status ?? '') === 'pago')
                                    <span class="badge-status badge-status--pago">✓ Pago</span>
                                @elseif(($c->status ?? '') === 'atrasado')
                                    <span class="badge-status badge-status--atrasado">Atrasado</span>
                                @else
                                    <span class="badge-status badge-status--pendente">Pendente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="ficha-empty">Sem cobranças associadas.</div>
        @endif
    </div>

</div>
@endsection
