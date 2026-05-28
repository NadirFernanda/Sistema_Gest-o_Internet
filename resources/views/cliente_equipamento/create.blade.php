@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        /* ── Cards ────────────────────────────────────────────── */
        .pf-card{background:#fff;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,.07);padding:22px 26px 24px;margin-bottom:14px;}
        .pf-card__header{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
        .pf-card__step{width:28px;height:28px;border-radius:50%;background:#f5a623;color:#fff;font-weight:800;font-size:.82rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .pf-card__title{font-weight:700;font-size:.97rem;color:#1a1a2e;}

        /* ── Fields ───────────────────────────────────────────── */
        .pf-field{margin-bottom:15px;}
        .pf-field:last-child{margin-bottom:0;}
        .pf-label{display:block;font-size:.75rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px;}
        .pf-input,.pf-select{width:100%;height:46px;padding:0 14px;border:1.5px solid #e8eaf0;border-radius:10px;font-size:.93rem;color:#222;background:#fff;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none;}
        .pf-input:focus,.pf-select:focus{outline:none;border-color:#f5a623;box-shadow:0 0 0 3px rgba(245,166,35,.13);}
        .pf-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23aaa' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:38px;cursor:pointer;}
        .pf-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        @media(max-width:540px){.pf-grid-2{grid-template-columns:1fr;}}
        .field-err{font-size:.8rem;color:#c0392b;margin-top:5px;}

        /* ── Stock badge ──────────────────────────────────────── */
        .stock-badge{display:inline-flex;align-items:center;gap:8px;background:#fff8ec;border:1.5px solid #f5a623;border-radius:10px;padding:9px 16px;margin-top:10px;font-size:.85rem;font-weight:600;color:#7a5200;}
        .stock-badge.unavailable{background:#fdecea;border-color:#f5bab5;color:#922b21;}

        /* ── Submit ───────────────────────────────────────────── */
        .pf-submit{width:100%;height:54px;background:#f5a623;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;letter-spacing:.02em;box-shadow:0 6px 22px rgba(245,166,35,.32);transition:opacity .15s,transform .1s;margin-top:6px;}
        .pf-submit:hover{opacity:.9;}
        .pf-submit:active{transform:scale(.99);}

        /* ── Alert ────────────────────────────────────────────── */
        .pf-alert{padding:12px 16px;border-radius:10px;font-size:.88rem;margin-bottom:14px;line-height:1.5;}
        .pf-alert--warn{background:#fff8ec;border:1px solid #f5dba0;color:#7a5200;}
        .pf-alert--warn ul{margin:4px 0 0;padding-left:18px;}

        /* ── Client info chip ─────────────────────────────────── */
        .client-chip{display:inline-flex;align-items:center;gap:10px;background:#f4f6f9;border-radius:10px;padding:10px 16px;margin-bottom:20px;}
        .client-chip__avatar{width:36px;height:36px;border-radius:50%;background:#f5a623;color:#fff;font-weight:800;font-size:.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .client-chip__name{font-weight:700;font-size:.92rem;color:#222;}
        .client-chip__bi{font-size:.78rem;color:#999;margin-top:1px;}

        /* ── Linked equipment table ───────────────────────────── */
        .eq-section{max-width:860px;margin:28px auto 0;}
        .eq-section__head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
        .eq-section__title{font-weight:700;font-size:1rem;color:#1a1a2e;}
        .eq-table-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow-x:auto;}
        .eq-table{width:100%;border-collapse:collapse;font-size:.87rem;}
        .eq-table thead{background:#f7f9fb;}
        .eq-table th{padding:10px 14px;text-align:left;font-size:.73rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.04em;border-bottom:2px solid #edf0f4;white-space:nowrap;}
        .eq-table td{padding:11px 14px;border-bottom:1px solid #f2f4f7;vertical-align:middle;}
        .eq-table tbody tr:last-child td{border-bottom:none;}
        .eq-table tbody tr:hover{background:#fafbfd;}
        .eq-empty td{text-align:center;color:#bbb;padding:36px;font-size:.9rem;}

        .badge-conn{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.74rem;font-weight:700;background:#e8f0fe;color:#3c5f9e;}
        .badge-dev{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.74rem;font-weight:700;background:#fff8ec;color:#d4820a;}
        .badge-warn{background:#fef9e7;color:#b7770d;}
        .badge-success{background:#e8f7ef;color:#2a8a55;}

        .abtn{width:31px;height:31px;border-radius:7px;border:none;cursor:pointer;font-size:.9rem;display:inline-flex;align-items:center;justify-content:center;transition:opacity .15s;}
        .abtn:hover{opacity:.72;}
        .abtn-edit{background:#fff8ec;border:1px solid #f5a623 !important;color:#d4820a;}
        .abtn-del{background:#fff5f5;border:1px solid #e05a4f !important;color:#e05a4f;}
        .abtn-hist{background:#f4f6f9;border:1px solid #dde3ec !important;color:#666;}
        .abtn-check{background:#e8f7ef;border:1px solid #a8e6c0 !important;color:#2a8a55;}

        .audit-row td{background:#f9fafb;padding:12px 16px;font-size:.83rem;color:#555;}
        .audit-row ul{margin:6px 0 0;padding-left:18px;}
        .audit-row li{margin-bottom:5px;}

        /* ── Select2 override ─────────────────────────────────── */
        .select2-container--default .select2-selection--single{height:46px !important;border:1.5px solid #e8eaf0 !important;border-radius:10px !important;padding:0 14px !important;display:flex;align-items:center;}
        .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:46px !important;font-size:.93rem;color:#222;padding:0;}
        .select2-container--default .select2-selection--single .select2-selection__arrow{height:44px !important;right:8px !important;}
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single{border-color:#f5a623 !important;box-shadow:0 0 0 3px rgba(245,166,35,.13) !important;}
        .select2-dropdown{border:1.5px solid #e8eaf0 !important;border-radius:10px !important;box-shadow:0 8px 28px rgba(0,0,0,.1) !important;}
        .select2-results__option--highlighted,.select2-results__option[aria-selected=true]{background:#f5a623 !important;color:#fff !important;}
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Vincular Equipamento',
        'subtitle' => 'Associar equipamento do estoque ao cliente',
        'heroCtAs' => '<a href="'.route('clientes.show', $cliente->id).'" class="btn btn-ghost">← Ficha do cliente</a>',
    ])

    <div style="max-width:660px; margin:24px auto 0; padding:0 16px;">

        {{-- Client context chip --}}
        <div class="client-chip">
            <div class="client-chip__avatar">{{ mb_strtoupper(mb_substr($cliente->nome, 0, 1)) }}</div>
            <div>
                <div class="client-chip__name">{{ $cliente->nome }}</div>
                <div class="client-chip__bi">{{ $cliente->bi ?? 'Sem documento' }}</div>
            </div>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="pf-alert pf-alert--warn">
                <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('cliente_equipamento.store', $cliente->id) }}" method="POST">
            @csrf

            {{-- ── Card 1: Equipamento ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">1</div>
                    <div class="pf-card__title">Equipamento</div>
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="estoque_equipamento_id">
                        Selecionar equipamento <span style="color:#e05a4f">*</span>
                    </label>
                    <select id="estoque_equipamento_id" name="estoque_equipamento_id" style="width:100%" data-no-choices="1">
                        <option value="">Pesquisar equipamento…</option>
                        @foreach($equipamentos as $equipamento)
                            @php $qty = (int) $equipamento->quantidade; @endphp
                            <option value="{{ $equipamento->id }}"
                                    data-quantidade="{{ $qty }}"
                                    {{ old('estoque_equipamento_id') == $equipamento->id ? 'selected' : '' }}
                                    @if($qty <= 0) disabled @endif>
                                {{ $equipamento->nome }} ({{ $equipamento->modelo }}) — estoque: {{ $qty }}@if($qty <= 0) ✗ Indisponível@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="quantidade">Quantidade <span style="color:#e05a4f">*</span></label>
                    <input type="number" id="quantidade" name="quantidade" class="pf-input"
                           min="1" value="{{ old('quantidade', 1) }}" style="max-width:160px;">
                    <div id="quantidade-error" class="field-err" style="display:none;"></div>
                    <div id="stock-badge" class="stock-badge" style="display:none;">
                        📦 Disponível em estoque: <strong id="estoque-quant">—</strong>
                    </div>
                    <div id="stock-unavail" class="stock-badge unavailable" style="display:none;">
                        ✕ Equipamento sem stock disponível
                    </div>
                    @if($errors->has('quantidade'))
                        <div class="field-err">{{ $errors->first('quantidade') }}</div>
                    @endif
                    @if($errors->has('estoque_equipamento_id'))
                        <div class="field-err">{{ $errors->first('estoque_equipamento_id') }}</div>
                    @endif
                </div>
            </div>

            {{-- ── Card 2: Instalação ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">2</div>
                    <div class="pf-card__title">Dados de instalação</div>
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="forma_ligacao">Forma de ligação <span style="color:#e05a4f">*</span></label>
                    <select id="forma_ligacao" name="forma_ligacao" class="pf-select" required>
                        <option value="">— Escolher forma de ligação —</option>
                        @foreach(['Ponto a Ponto', 'Multiponto', 'Fibra', 'V-Sat'] as $fl)
                            <option value="{{ $fl }}" {{ old('forma_ligacao') == $fl ? 'selected' : '' }}>{{ $fl }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('forma_ligacao'))
                        <div class="field-err">{{ $errors->first('forma_ligacao') }}</div>
                    @endif
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="morada">Morada <span style="color:#e05a4f">*</span></label>
                    <input type="text" id="morada" name="morada" class="pf-input"
                           placeholder="Ex: Rua dos Coqueiros, nº 47, Bairro Palanca"
                           value="{{ old('morada') }}" required>
                    @if($errors->has('morada'))
                        <div class="field-err">{{ $errors->first('morada') }}</div>
                    @endif
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="ponto_referencia">Ponto de referência <span style="color:#e05a4f">*</span></label>
                    <input type="text" id="ponto_referencia" name="ponto_referencia" class="pf-input"
                           placeholder="Ex: Em frente ao mercado da Palanca"
                           value="{{ old('ponto_referencia') }}" required>
                    @if($errors->has('ponto_referencia'))
                        <div class="field-err">{{ $errors->first('ponto_referencia') }}</div>
                    @endif
                </div>
            </div>

            <button type="submit" class="pf-submit">Vincular Equipamento</button>
        </form>
    </div>

    {{-- ── Equipamentos já vinculados ── --}}
    <div class="eq-section" style="padding:0 16px 56px;">
        <div class="eq-section__head">
            <div class="eq-section__title">Equipamentos vinculados a {{ $cliente->nome }}</div>
            @can('clientes.devolucao')
                @if($vinculados->count())
                    <form action="{{ route('cliente.solicitar_devolucao', $cliente->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="prazo_dias" value="7">
                        <button type="submit" class="btn btn-ghost" style="font-size:.82rem;"
                            onclick="return confirm('Solicitar devolução de todos os equipamentos deste cliente?')">
                            Solicitar devolução (todos)
                        </button>
                    </form>
                @endif
            @endcan
        </div>

        <div class="eq-table-card">
            <table class="eq-table">
                <thead>
                    <tr>
                        <th>Equipamento</th>
                        <th>Ligação</th>
                        <th>Morada</th>
                        <th>Ponto ref.</th>
                        <th style="text-align:center;">Qtd.</th>
                        <th>Acções</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vinculados as $v)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#222;">{{ $v->equipamento->nome }}</div>
                                <div style="font-size:.78rem;color:#aaa;">{{ $v->equipamento->modelo }}</div>
                            </td>
                            <td><span class="badge-conn">{{ $v->forma_ligacao ?? '—' }}</span></td>
                            <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $v->morada }}">{{ $v->morada }}</td>
                            <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $v->ponto_referencia }}">{{ $v->ponto_referencia }}</td>
                            <td style="text-align:center;font-weight:700;">{{ $v->quantidade ?? 1 }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('cliente_equipamento.edit', [$cliente->id, $v->id]) }}"
                                   class="abtn abtn-edit" title="Editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                                </a>
                                <form action="{{ route('cliente_equipamento.destroy', [$cliente->id, $v->id]) }}"
                                      method="POST" style="display:inline-block; margin-left:4px;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="abtn abtn-del" title="Remover"
                                        onclick="return confirm('Remover este equipamento do cliente?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    </button>
                                </form>
                                <button type="button" class="abtn abtn-hist" title="Histórico"
                                    style="margin-left:4px;"
                                    onclick="var r=document.getElementById('audit-{{ $v->id }}');r.style.display=r.style.display==='none'?'table-row':'none';">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </button>
                                @if(isset($v->status) && $v->status === 'devolucao_solicitada')
                                    <span class="badge-dev badge-warn" style="margin-left:6px;">Devolução pendente</span>
                                    @can('clientes.devolucao')
                                        <form action="{{ route('cliente_equipamento.registrar_devolucao', [$cliente->id, $v->id]) }}"
                                              method="POST" style="display:inline-block; margin-left:4px;">
                                            @csrf
                                            <button type="submit" class="abtn abtn-check" title="Confirmar devolução"
                                                onclick="return confirm('Registar devolução recebida?')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                        </form>
                                    @endcan
                                @elseif(isset($v->status) && $v->status === 'emprestado')
                                    <span class="badge-dev" style="margin-left:6px;">Emprestado</span>
                                @endif
                            </td>
                        </tr>
                        <tr id="audit-{{ $v->id }}" class="audit-row" style="display:none;">
                            <td colspan="6">
                                <strong>Histórico (últimas 5 acções)</strong>
                                <ul>
                                    @forelse(($auditsGrouped[$v->id] ?? collect())->take(5) as $a)
                                        <li>
                                            <strong>{{ $a->action ?? 'acção' }}</strong>
                                            — <span style="color:#888;">{{ $a->actor_name ?? 'sistema' }}</span>
                                            <span style="color:#bbb;">({{ optional($a->created_at)->format('d/m/Y H:i') }})</span>
                                        </li>
                                    @empty
                                        <li style="color:#bbb;">Sem histórico disponível.</li>
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr class="eq-empty"><td colspan="6">Nenhum equipamento vinculado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if(isset($equipamentoDuplicadoMsg))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    alert(@json($equipamentoDuplicadoMsg));
});
</script>
@endpush
@endif
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function ($) {

    var sel = $('#estoque_equipamento_id');

    sel.select2({
        placeholder: 'Pesquisar equipamento…',
        allowClear: true,
        width: '100%',
    });

    function updateStock() {
        var opt   = sel.find('option:selected');
        var avail = parseInt(opt.data('quantidade'), 10) || 0;
        var badge   = document.getElementById('stock-badge');
        var unavail = document.getElementById('stock-unavail');
        var quantEl = document.getElementById('quantidade');

        if (!opt.val()) {
            badge.style.display = 'none';
            unavail.style.display = 'none';
            return;
        }

        document.getElementById('estoque-quant').textContent = avail;

        if (avail <= 0) {
            badge.style.display   = 'none';
            unavail.style.display = 'inline-flex';
            quantEl.setAttribute('disabled', true);
            quantEl.value = 0;
        } else {
            badge.style.display   = 'inline-flex';
            unavail.style.display = 'none';
            quantEl.removeAttribute('disabled');
            quantEl.setAttribute('max', avail);
            var cur = parseInt(quantEl.value, 10) || 0;
            if (cur > avail) {
                quantEl.value = avail;
                document.getElementById('quantidade-error').textContent = 'Ajustado ao máximo disponível: ' + avail;
                document.getElementById('quantidade-error').style.display = 'block';
            } else {
                document.getElementById('quantidade-error').style.display = 'none';
            }
        }
    }

    window.updateEstoqueInfo = updateStock;
    sel.on('select2:select select2:unselect change', updateStock);
    $(document).on('change', '#estoque_equipamento_id', updateStock);
    updateStock();

    var quantEl = document.getElementById('quantidade');
    if (quantEl) {
        quantEl.addEventListener('input', function () {
            this.setCustomValidity('');
            var max = parseInt(this.getAttribute('max') || '0', 10);
            var val = parseInt(this.value || '0', 10);
            if (max > 0 && val > max) {
                this.setCustomValidity('Máximo disponível: ' + max);
            } else if (val < 1) {
                this.setCustomValidity('Mínimo: 1');
            }
        });
    }
});
</script>
@endpush
