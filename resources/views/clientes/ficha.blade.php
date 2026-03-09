@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush
<div class="clientes-container ficha-cliente">
    {{-- Compensação controls moved to plano detail view to avoid duplication --}}

    {{-- Cabeçalho da ficha com logotipo --}}
    <div class="ficha-header">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logotipo" class="logo">
        <h1 class="ficha-title">Ficha do Cliente</h1>
        <p class="mb-0 muted">Emitido: {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dados do Cliente</div>
                <div class="card">
                    <div class="card-header">Dados do Cliente</div>
                    <div class="card-body">
                        <div class="ficha-grid">
                            <div><span class="muted">Nome / Razão social:</span><br><strong>{{ $cliente->nome }}</strong></div>
                            <div><span class="muted">BI / NIF:</span><br><strong>{{ $cliente->bi }}</strong></div>
                            <div><span class="muted">Contacto (WhatsApp):</span><br><strong>{{ $cliente->contato }}</strong></div>
                            <div><span class="muted">Email:</span><br><strong>{{ $cliente->email }}</strong></div>
                            <div><span class="muted">Estado:</span><br><strong>{{ $cliente->estado ?? '—' }}</strong></div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Observações</div>
                    <div class="card-body">
                        <p style="font-size:1.04rem;line-height:1.5;color:#444;">{!! nl2br(e($cliente->observacoes ?? '')) !!}</p>
                    </div>
                </div>
        <div class="col-md-6">
            <div class="card mb-3 shadow-sm">
                <div class="card-header">Equipamentos Associados</div>
                <div class="card-body p-0">
                    @if((isset($cliente->equipamentos) && $cliente->equipamentos->count()) || (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count()))
                    <div class="table-responsive ficha-equip-table">
                        <table class="tabela-estoque-moderna table">
                            <colgroup>
                                <col style="width:16%">
                                <col style="width:28%">
                                <col style="width:16%">
                                <col style="width:18%">
                                <col style="width:10%">
                                <col style="width:12%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="text-center">Marca</th>
                                    <th class="text-center">Descrição</th>
                                    <th class="text-center">Modelo</th>
                                    <th class="text-center">Nº Série</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                                    @php $est = $vinc->equipamento; @endphp
                                    <tr class="{{ $loop->odd ? 'odd-row' : 'even-row' }}">
                                        <td class="text-center">{{ $est->nome ?? '-' }}</td>
                                        <td class="text-center">{{ $est->descricao ?? '-' }}</td>
                                        <td class="text-center">{{ $est->modelo ?? '-' }}</td>
                                        <td class="text-center">{{ $est->numero_serie ?? '-' }}</td>
                                        <td class="text-center">{{ $vinc->quantidade ?? '1' }}</td>
                                        <td class="text-center nowrap">
                                            <a href="{{ route('cliente_equipamento.edit', [$cliente->id, $vinc->id]) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                            </a>
                                            <form action="{{ route('cliente_equipamento.destroy', [$cliente->id, $vinc->id]) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-icon btn-danger" title="Apagar" aria-label="Apagar" onclick="return confirm('Deseja desvincular este equipamento?')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <p class="p-3 mb-0">Nenhum equipamento cadastrado para este cliente.</p>
                    @endif
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Planos Contratados</div>
                <div class="card-body p-3">
                    @if(isset($cliente->planos) && $cliente->planos->count())
                        <div id="planosLista" class="plan-grid">
                            @foreach($cliente->planos as $pl)
                                @php
                                    try {
                                        $dataAtiv = !empty($pl->data_ativacao) ? \Carbon\Carbon::parse($pl->data_ativacao)->startOfDay() : null;
                                        if (!empty($pl->proxima_renovacao)) {
                                            $dataTerm = \Carbon\Carbon::parse($pl->proxima_renovacao)->startOfDay();
                                        } elseif ($dataAtiv && $pl->ciclo) {
                                            $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$pl->ciclo));
                                            if ($cicloInt <= 0) { $cicloInt = (int)$pl->ciclo; }
                                            $dataTerm = $dataAtiv->copy()->addDays($cicloInt - 1)->startOfDay();
                                        } else {
                                            $dataTerm = null;
                                        }
                                    } catch (\Exception $e) {
                                        $dataTerm = null;
                                    }
                                    $hoje = \Carbon\Carbon::today();
                                    $cicloShown = $pl->ciclo ?? '-';
                                    $preco = isset($pl->preco) ? number_format($pl->preco,2,',','.') . ' Kz' : '-';
                                    $estado = $pl->estado ?? '-';
                                    $diasRest = $dataTerm ? $hoje->diffInDays($dataTerm, false) : null;
                                    $totalCiclo = null;
                                    $percent = 0;
                                    if ($dataAtiv && $dataTerm) {
                                        $totalCiclo = max(1, $dataAtiv->diffInDays($dataTerm) + 1);
                                        $passed = max(0, min($totalCiclo, $hoje->diffInDays($dataAtiv)) );
                                        $percent = (int) floor(($passed / $totalCiclo) * 100);
                                        if ($percent < 0) $percent = 0; if ($percent > 100) $percent = 100;
                                    }
                                @endphp
                                <div class="plan-card">
                                    <div class="plan-meta">
                                        <div>
                                            <div class="plan-title">{{ $pl->nome ?? 'Plano #' . $pl->id }}</div>
                                            <div class="muted small" style="margin-top:4px;">ID: {{ $pl->id }} • Estado: <span class="chip chip--accent">{{ $estado }}</span></div>
                                        </div>
                                        <div style="text-align:right;min-width:120px;">
                                            <div class="plan-price">{{ $preco }}</div>
                                            <div class="muted" style="font-size:0.86rem;">Ciclo: {{ $cicloShown }}</div>
                                        </div>
                                    </div>

                                    <div class="plan-dates">
                                        <div>
                                            <div class="muted">Data Ativação</div>
                                            <div class="plan-date">{{ $dataAtiv ? $dataAtiv->format('d/m/Y') : '—' }}</div>
                                        </div>
                                        <div>
                                            <div class="muted">Próxima Renovação / Término</div>
                                            <div class="plan-date">{{ $dataTerm ? $dataTerm->format('d/m/Y') : '—' }}</div>
                                        </div>
                                    </div>

                                    <div class="plan-progress">
                                        <div class="progress"><div class="progress-bar" style="width:{{ $percent }}%"></div></div>
                                        <div class="muted small">{{ $totalCiclo ? $percent . '% do ciclo decorrido' : 'Progresso indisponível' }} @if(!is_null($diasRest)) • {{ $diasRest >= 0 ? $diasRest . ' dias restantes' : abs($diasRest) . ' dias vencido' }} @endif</div>
                                    </div>

                                    <div class="plan-actions">
                                        <a href="{{ route('clientes.show', $cliente->id) }}?plano={{ $pl->id }}" class="btn btn-sm btn-ghost">Ver Detalhes</a>
                                        <form method="POST" action="{{ route('clientes.adicionar_janela', $cliente->id) }}" style="display:inline-block;">
                                            @csrf
                                            <input type="hidden" name="plano_id" value="{{ $pl->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">Adicionar Janela</button>
                                        </form>
                                        <button onclick="document.getElementById('compensar-dias-plano-{{ $pl->id }}').classList.add('active')" class="btn btn-sm btn-ghost">Compensar Dias</button>
                                    </div>

                                    {{-- Modal mínimo por plano (apenas se necessário) --}}
                                    <div id="compensar-dias-plano-{{ $pl->id }}" class="modal-overlay" aria-hidden="true">
                                        <div class="modal">
                                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                                <strong>Compensar dias — {{ $pl->nome ?? 'Plano' }}</strong>
                                                <button onclick="document.getElementById('compensar-dias-plano-{{ $pl->id }}').classList.remove('active')" class="btn btn-ghost">×</button>
                                            </div>
                                            <form method="POST" action="{{ route('clientes.compensar_dias', $cliente->id) }}">
                                                @csrf
                                                <input type="hidden" name="plano_id" value="{{ $pl->id }}">
                                                <label class="muted">Dias a compensar</label>
                                                <input name="dias_compensados" id="dias_compensados" type="number" min="1" max="90" class="form-control" style="margin-top:6px;margin-bottom:10px;padding:8px;border:1px solid #ddd;border-radius:6px;">
                                                <div style="display:flex;gap:8px;justify-content:flex-end;">
                                                    <button type="button" onclick="document.getElementById('compensar-dias-plano-{{ $pl->id }}').classList.remove('active')" class="btn btn-ghost">Cancelar</button>
                                                    <button type="submit" class="btn btn-cta">Confirmar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="p-3 mb-0">Nenhum plano contratado.</p>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Cobranças (pendentes / recentes)</div>
                <div class="card-body p-0">
                    @if($cliente->cobrancas && $cliente->cobrancas->count())
                    <table class="table mb-0">
                        <thead>
                                <tr>
                                <th>Nº</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->cobrancas as $c)
                            <tr>
                                <td>{{ $c->id }}</td>
                                <td>{{ $c->descricao ?? '-' }}</td>
                                <td>{{ number_format($c->valor, 2, ',', '.') }} Kz</td>
                                <td>{{ $c->data_vencimento ? \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') : 'Sem data' }}</td>
                                <td>
                                    @if(isset($c->status) && $c->status === 'pago')
                                        <span class="badge-cobrancas pago">Pago</span>
                                    @elseif(isset($c->status) && $c->status === 'atrasado')
                                        <span class="badge-cobrancas">Atrasado</span>
                                    @else
                                        <span class="badge-cobrancas pendente">Pendente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="p-3 mb-0">Sem cobranças associadas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Modal logic for Compensar Dias
    const btnCompensar = document.getElementById('compensar-dias-btn');
    const modal = document.getElementById('modal-compensar-dias');
    const fechar = document.getElementById('fechar-modal-compensar');
    if(btnCompensar && modal && fechar){
        btnCompensar.onclick = () => { modal.style.display = 'flex'; document.getElementById('dias_compensados').focus(); };
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
@endsection
