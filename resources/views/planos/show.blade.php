@extends('layouts.app')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    @endpush

    <div class="plan-show-card">
        @php
            use App\Models\Cobranca;
            $cliente = $plano->cliente ?? null;
            // compensacoes table uses plano_id (not cliente_id) — query via all client planos
            $compCount = 0;
            try {
                if ($cliente) {
                    $planoIds = $cliente->planos->pluck('id');
                    $compCount = $planoIds->isNotEmpty()
                        ? \DB::table('compensacoes')->whereIn('plano_id', $planoIds)->count()
                        : 0;
                }
            } catch (\Exception $e) {
                $compCount = 0;
            }
        @endphp

        {{-- plan header will be rendered inside the details card below for visual cohesion --}}

        {{-- Detalhes do plano: data ativação, próxima renovação, ciclo, dias restantes, template, cliente, último pagamento --}}
        @php
            // calcula datas e dias restantes
            try {
                $dataAtiv = !empty($plano->data_ativacao) ? \Carbon\Carbon::parse($plano->data_ativacao)->startOfDay() : null;
                if (!empty($plano->proxima_renovacao)) {
                    $dataTerm = \Carbon\Carbon::parse($plano->proxima_renovacao)->startOfDay();
                } elseif ($dataAtiv && $plano->ciclo) {
                    $cicloInt = intval(preg_replace('/[^0-9]/','', (string)$plano->ciclo));
                    if ($cicloInt <= 0) { $cicloInt = (int) $plano->ciclo; }
                    $dataTerm = $dataAtiv->copy()->addDays($cicloInt - 1)->startOfDay();
                } else {
                    $dataTerm = null;
                }
            } catch (\Exception $e) {
                $dataAtiv = null; $dataTerm = null;
            }
            $diasRest = $dataTerm ? \Carbon\Carbon::today()->diffInDays($dataTerm, false) : null;
            // último pagamento: inclui cobranças com data_pagamento OU status=pago
            try {
                $clienteId = $plano->cliente_id ?? ($plano->cliente->id ?? null);
                $ultimoPagamento = $clienteId
                    ? Cobranca::where('cliente_id', $clienteId)
                        ->where(function($q) {
                            $q->whereNotNull('data_pagamento')->orWhere('status', 'pago');
                        })
                        ->orderByDesc('data_pagamento')
                        ->orderByDesc('data_vencimento')
                        ->first()
                    : null;
            } catch (\Exception $e) {
                $ultimoPagamento = null;
            }
        @endphp

        <div class="plan-show-grid" style="margin-top:18px;">
            <div class="card">
                <div class="card-header">Detalhes do Plano</div>
                <div class="card-body">
                    <div class="plan-summary two-column in-card">
                        <div class="plan-summary-left">
                            <div style="margin-bottom:6px;color:#333;font-size:0.98rem;">Plano: <strong>{{ $plano->nome }}</strong></div>
                            <p style="margin:6px 0 12px 0;color:#333;">{{ $plano->descricao }}</p>
                            <div class="plan-status">
                                <div style="color:#666;margin-bottom:6px;">Status:</div>
                                <div style="font-weight:800;font-size:1.05rem;">{{ $plano->estado ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="plan-summary-right">
                            <div class="price-row" style="margin-bottom:6px;">
                                <div style="font-size:2rem;font-weight:800;">Kz {{ isset($plano->preco) ? number_format($plano->preco,2,',','.') : '-' }}</div>
                                <div style="background:#f0f0f0;padding:8px 12px;border-radius:999px;font-weight:700;color:#333;">{{ $plano->ciclo ?? '30' }} dias</div>
                            </div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <div class="muted">Data de Ativação</div>
                            <div>{{ $dataAtiv ? $dataAtiv->format('d/m/Y') : '—' }}</div>
                        </div>
                        <div>
                            <div class="muted">Próxima Renovação / Término</div>
                            <div>{{ $dataTerm ? $dataTerm->format('d/m/Y') : '—' }}</div>
                        </div>
                        <div>
                            <div class="muted">Ciclo (dias)</div>
                            <div>{{ $plano->ciclo ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="muted">Dias restantes</div>
                            <div>{{ !is_null($diasRest) ? ($diasRest >= 0 ? $diasRest . ' dias' : abs($diasRest) . ' dias vencido') : '—' }}</div>
                        </div>
                        <div style="grid-column:1 / -1;">
                            <div class="muted">Template</div>
                            <div>{{ optional($plano->template)->name ?? ($plano->nome ?? '—') }}</div>
                        </div>
                        <div>
                            <div class="muted">Cliente</div>
                            <div>{{ $cliente ? $cliente->nome . ' — ' . ($cliente->contato ?? '') : '—' }}</div>
                        </div>
                        <div>
                            <div class="muted">Compensações registradas (cliente)</div>
                            <div>{{ $compCount }}</div>
                        </div>
                        <div>
                            <div class="muted">Último pagamento</div>
                            <div>@if($ultimoPagamento)
                                @php
                                    $dpDate = $ultimoPagamento->data_pagamento
                                        ? \Carbon\Carbon::parse($ultimoPagamento->data_pagamento)->format('d/m/Y')
                                        : ($ultimoPagamento->data_vencimento ? \Carbon\Carbon::parse($ultimoPagamento->data_vencimento)->format('d/m/Y') : null);
                                @endphp
                                {{ $dpDate ? $dpDate . ' — ' . number_format($ultimoPagamento->valor,2,',','.').' Kz' : '—' }}
                            @else
                                —
                            @endif</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metadados removidos a pedido do usuário -->
        
            <!-- CTA buttons: centered under the left column -->
            <div class="plan-actions-wrapper">
                <div class="cta-grid">
                        <!-- Order set column-first so each column has 2 buttons: top then bottom -->
                        <button type="button" onclick="location.href='{{ $plano->cliente_id ? route('clientes.compensacoes', $plano->cliente_id) : '#' }}'" class="btn btn-cta">Histórico de Compensações</button>
                        <a href="{{ route('planos.index') }}" class="btn btn-ghost">Voltar</a>

                        <button id="compensar-dias-btn" class="btn btn-cta">Compensar Dias</button>
                        <form action="{{ route('planos.destroy', $plano->id) }}" method="POST" onsubmit="return confirm('Apagar plano?');" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-secondary" type="submit">Apagar</button>
                        </form>
                        <button id="adicionar-janela-btn" class="btn btn-cta">Adicionar Janela</button>
                        <a href="{{ route('planos.edit', $plano->id) }}" class="btn btn-warning">Editar</a>
                </div>
            </div>

        <!-- Modal para compensar dias -->
        <div id="modal-compensar-dias" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
            <div style="background:#fff;padding:32px 28px 24px 28px;border-radius:14px;max-width:380px;width:96vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;flex-direction:column;align-items:center;">
                <h5 style="margin-bottom:18px;">Compensar Dias ao Plano</h5>
                <form id="form-compensar-dias" method="POST" action="{{ route('clientes.compensar_dias', $plano->cliente_id) }}">
                    @csrf
                    <label for="dias_compensados" style="font-weight:600;">Dias a compensar:</label>
                    <input type="number" min="1" max="90" name="dias_compensados" id="dias_compensados" class="form-control" style="margin:10px 0 18px 0;width:120px;text-align:center;" required>
                    <button type="submit" class="btn btn-primary" style="min-width:120px;">Salvar</button>
                    <button type="button" id="fechar-modal-compensar" class="btn btn-ghost" style="margin-left:10px;">Cancelar</button>
                </form>
            </div>
        </div>

        <!-- Modal para adicionar janela automática -->
        <div id="modal-adicionar-janela" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
            <div style="background:#fff;padding:24px 20px;border-radius:12px;max-width:520px;width:96vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;flex-direction:column;">
                <h5 style="margin-bottom:12px;">Adicionar Janela automática</h5>
                <p style="margin:0 0 12px 0;color:#444;">Selecione o plano que deseja estender. Se não escolher, será usado o plano ativo mais recente.</p>
                <form id="form-adicionar-janela" method="POST" action="{{ route('clientes.adicionar_janela', $plano->cliente_id) }}">
                    @csrf
                    <div style="max-height:220px;overflow:auto;margin-bottom:12px;">
                        @foreach($plano->cliente->planos ?? [] as $pl)
                            <label style="display:flex;align-items:center;gap:12px;padding:8px;border-radius:8px;border:1px solid #f0f0f0;margin-bottom:6px;">
                                <input type="radio" name="plano_id" value="{{ $pl->id }}">
                                <div style="flex:1;">
                                    <div style="font-weight:700;">{{ $pl->nome }}</div>
                                    @php
                                        $proximaDisplay = '—';
                                        if (!empty($pl->proxima_renovacao)) {
                                            $proximaDisplay = \Carbon\Carbon::parse($pl->proxima_renovacao)->format('d/m/Y');
                                        } elseif (!empty($pl->data_ativacao) && !empty($pl->ciclo)) {
                                            $cicloInt = intval(preg_replace('/\D/', '', (string) $pl->ciclo));
                                            if ($cicloInt <= 0) { $cicloInt = (int) $pl->ciclo ?: 30; }
                                            $proximaDisplay = \Carbon\Carbon::parse($pl->data_ativacao)->addDays($cicloInt - 1)->format('d/m/Y');
                                        }
                                    @endphp
                                    <div class="muted">Ativação: {{ $pl->data_ativacao ? \Carbon\Carbon::parse($pl->data_ativacao)->format('d/m/Y') : '—' }} • Ciclo: {{ $pl->ciclo ?? '—' }} dias • Próx: {{ $proximaDisplay }}</div>
                                </div>
                            </label>
                        @endforeach
                        @if(empty($plano->cliente->planos) || $plano->cliente->planos->isEmpty())
                            <p class="p-2 muted">Nenhum plano encontrado para este cliente.</p>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary">Adicionar Janela</button>
                        <button type="button" id="fechar-modal-janela" class="btn btn-ghost">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Modal logic for Compensar Dias
    const btnCompensar = document.getElementById('compensar-dias-btn');
    const modal = document.getElementById('modal-compensar-dias');
    const fechar = document.getElementById('fechar-modal-compensar');
    if(btnCompensar && modal && fechar){
        btnCompensar.onclick = () => { modal.style.display = 'flex'; const el = document.getElementById('dias_compensados'); if(el) el.focus(); };
        fechar.onclick = () => { modal.style.display = 'none'; };
        modal.onclick = (e) => { if(e.target === modal) modal.style.display = 'none'; };
    }

    // Modal logic for Adicionar Janela
    const btnJanela = document.getElementById('adicionar-janela-btn');
    const modalJanela = document.getElementById('modal-adicionar-janela');
    const fecharJanela = document.getElementById('fechar-modal-janela');
    if (btnJanela && modalJanela && fecharJanela) {
        btnJanela.onclick = () => { modalJanela.style.display = 'flex'; };
        fecharJanela.onclick = () => { modalJanela.style.display = 'none'; };
        modalJanela.onclick = (e) => { if (e.target === modalJanela) modalJanela.style.display = 'none'; };
    }
});
</script>
@endpush
