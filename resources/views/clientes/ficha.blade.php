@extends('layouts.app')

<div style="color:red;font-size:22px;text-align:center;">TESTE FICHA - PRODUÇÃO</div>

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush
<div class="container">
    <style>
        @media print { .no-print { display: none !important; } }
        :root { --muted:#6b6b6b; --accent:#f7b500; --soft:#f6f7fb; }
        body, .container, .ficha-cliente { font-family:'Segoe UI', 'Roboto', Arial, sans-serif; background:#f7f8fa; }
        .ficha-header { max-width:980px; margin:28px auto 16px; text-align:center; }
        .ficha-header .ficha-logo { display:block; margin:0 auto 10px; max-width:120px; height:auto; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
        .ficha-cliente { max-width:980px; margin:24px auto; }
        .section-title { font-weight:800; font-size:1.25rem; margin:18px 0 16px; color:#f7b500; letter-spacing:0.5px; }
        .muted { color:var(--muted); font-size:0.97rem; }

        /* Card and header */
        .card { border:1px solid #e9ecef; border-radius:14px; overflow:hidden; background:#fff; box-shadow:0 2px 16px rgba(0,0,0,0.06); margin-bottom:22px; }
        .card-header { background:#fffbe7; color:#f7b500; font-weight:800; font-size:1.08rem; padding:14px 20px; border-bottom:1px solid #f7b500; letter-spacing:0.5px; }
        .card-body { padding:18px 22px; font-size:1.05rem; }

        /* Tables: fixed layout, readable spacing and wrapping */
        .table { width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed; font-size:0.97rem; }
        .table th, .table td { padding:10px 12px; border:1px solid #f0f0f0; vertical-align:middle; word-wrap:break-word; overflow-wrap:break-word; }
        .table thead th { background:#fffbe7; font-weight:800; color:#f7b500; border-bottom:2px solid #f7b500; }
        .table tbody tr td { background:#fff; }
        .table tbody tr:nth-child(odd) td { background:#fcfcfd; }

        /* Column helpers for better desktop widths */
        .col-id { width:6%; }
        .col-nome { width:28%; }
        .col-modelo { width:16%; }
        .col-serie { width:15%; }
        .col-quant { width:8%; text-align:center; }
        .col-morada { width:27%; }

        /* Badges */
        .badge-planos, .badge-cobrancas { display:inline-block; padding:4px 12px; border-radius:999px; font-size:0.95rem; color:#fff; background: #f7b500; font-weight:700; letter-spacing:0.2px; }
        .badge-cobrancas.pago { background: #4caf50; color:#fff; }
        .badge-cobrancas.pendente { background: #f7b500; color:#fff; }
        .badge-cobrancas { background: #e0e0e0; color:#222; }

        /* Modern row/col spacing */
        .row { margin-bottom:18px; }
        .card + .card { margin-top:18px; }
        .card-body p { margin-bottom:10px; }
    </style>
    {{-- Toolbar com ações acima do cartão (não aparece na impressão) --}}
    <div class="ficha-toolbar no-print" style="max-width:980px;margin:0 auto 12px;">
        <div style="display:flex;gap:10px;flex-direction:row;justify-content:flex-end;align-items:center;">
            <!-- Botão único: Compensar Dias -->
            <button id="compensar-dias-btn" class="btn btn-warning" style="padding:12px 22px; font-size:1.05rem; border-radius:8px; min-width:200px; font-weight:700;">
                Compensar Dias
            </button>
        </div>

        <!-- Modal para compensar dias -->
        <div id="modal-compensar-dias" style="display:none;position:fixed;z-index:2000;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.32);align-items:center;justify-content:center;">
            <div style="background:#fff;padding:32px 28px 24px 28px;border-radius:14px;max-width:380px;width:96vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;flex-direction:column;align-items:center;">
                <h5 style="margin-bottom:18px;">Compensar Dias ao Plano</h5>
                <form id="form-compensar-dias" method="POST" action="{{ route('clientes.compensar_dias', $cliente->id) }}">
                    @csrf
                    <label for="dias_compensados" style="font-weight:600;">Dias a compensar:</label>
                    <input type="number" min="1" max="90" name="dias_compensados" id="dias_compensados" class="form-control" style="margin:10px 0 18px 0;width:120px;text-align:center;" required>
                    <button type="submit" class="btn btn-primary" style="min-width:120px;">Salvar</button>
                    <button type="button" id="fechar-modal-compensar" class="btn btn-ghost" style="margin-left:10px;">Cancelar</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Cabeçalho da ficha com logotipo --}}
    <div class="ficha-header">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logotipo" class="ficha-logo">
        <h2 style="margin-top:10px;font-weight:900;color:#222;letter-spacing:0.5px;">Ficha do Cliente</h2>
        <p class="mb-0 muted">Emitido: {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dados do Cliente</div>
                <div class="card">
                    <div class="card-header">Dados do Cliente</div>
                    <div class="card-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px 24px;">
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
            <div class="card mb-3 shadow-sm" style="border-radius:12px;overflow:hidden;">
                <div class="card-header" style="background:#fffbe7;color:#f7b500;font-weight:700;font-size:1.08rem;letter-spacing:0.5px;">Equipamentos Associados</div>
                <div class="card-body p-0">
                    @if((isset($cliente->equipamentos) && $cliente->equipamentos->count()) || (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count()))
                    <div class="table-responsive">
                        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;table-layout:fixed;">
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
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:700;border-bottom:2px solid #f7b500;font-size:0.98rem;padding:8px 4px;">Marca</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:700;border-bottom:2px solid #f7b500;font-size:0.98rem;padding:8px 4px;">Descrição</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:700;border-bottom:2px solid #f7b500;font-size:0.98rem;padding:8px 4px;">Modelo</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:700;border-bottom:2px solid #f7b500;font-size:0.98rem;padding:8px 4px;">Nº Série</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:700;border-bottom:2px solid #f7b500;font-size:0.98rem;padding:8px 4px;">Qtd</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:700;border-bottom:2px solid #f7b500;font-size:0.98rem;padding:8px 4px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                                    @php $est = $vinc->equipamento; @endphp
                                    <tr style="background:{{ $loop->odd ? '#fcfcfd' : '#fff' }};">
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->nome ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->descricao ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->modelo ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->numero_serie ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $vinc->quantidade ?? '1' }}</td>
                                        <td style="white-space:nowrap;text-align:center;vertical-align:middle;">
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
                <div class="card-body p-0">
                    @if(isset($cliente->planos) && $cliente->planos->count())
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Nº</th>
                                    <th>Nome do Plano</th>
                                    <th>Data Ativação</th>
                                    <th>Ciclo (dias)</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->planos as $pl)
                                    <tr>
                                        <td>{{ $pl->id }}</td>
                                        <td>{{ $pl->nome ?? '-' }}</td>
                                        <td>{{ $pl->data_ativacao ? \Carbon\Carbon::parse($pl->data_ativacao)->format('d/m/Y') : 'Sem data' }}</td>
                                        <td>{{ $pl->ciclo ?? '-' }}</td>
                                        <td><span class="badge-planos">{{ $pl->estado ?? '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
});
</script>
@endpush
@endsection
