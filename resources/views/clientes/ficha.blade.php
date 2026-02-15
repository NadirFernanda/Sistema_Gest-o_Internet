@extends('layouts.app')

@section('content')
<div class="container">
    <style>
        @media print { .no-print { display: none !important; } }
        :root { --muted:#6b6b6b; --accent:#f7b500; --soft:#f6f7fb; }
        .ficha-header { max-width:980px; margin:18px auto 6px; text-align:center; }
        .ficha-header .ficha-logo { display:block; margin:0 auto 6px; max-width:120px; height:auto; }
        .ficha-cliente { max-width:980px; margin:12px auto; }
        .cliente-dados-moderna { padding:18px 22px; }

        /* Card and header */
        .card { border:1px solid #e9ecef; border-radius:6px; overflow:hidden; background:#fff; }
        .card-header { background:#fbfbfd; color:#222; font-weight:700; padding:10px 14px; border-bottom:1px solid #eef0f3; }
        .card-body { padding:12px 14px; }

        /* Tables: fixed layout, readable spacing and wrapping */
        .table { width:100%; border-collapse:separate; border-spacing:0; table-layout:fixed; font-size:0.95rem; }
        .table th, .table td { padding:8px 10px; border:1px solid #f0f0f0; vertical-align:top; word-wrap:break-word; overflow-wrap:break-word; }
        .table thead th { background:var(--soft); font-weight:700; color:#222; }
        .table tbody tr td { background:#fff; }
        .table tbody tr:nth-child(odd) td { background:#fcfcfd; }

        /* Column helpers for better desktop widths */
        .col-id { width:6%; }
        .col-nome { width:28%; }
        .col-modelo { width:16%; }
        .col-serie { width:15%; }
        .col-quant { width:8%; text-align:center; }
        .col-morada { width:27%; }

        .section-title { font-weight:700; margin:8px 0 10px; text-align:left; }
        .muted { color:var(--muted); font-size:0.9rem; }
        /* Badges */
        .badge-planos, .badge-cobrancas { display:inline-block; padding:4px 8px; border-radius:999px; font-size:0.85rem; color:#111; background: transparent; }
        .badge-cobrancas { background: transparent; }
        .badge-cobrancas.pago { background: transparent; color:#111; }
        .badge-cobrancas.pendente { background: transparent; color:#111; }
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
    <div class="ficha-header" style="max-width:900px;margin:12px auto 0;text-align:center;">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logotipo" class="ficha-logo" style="max-width:120px;height:auto;display:block;margin:0 auto 8px;">
        <h4 style="margin-top:8px;">Ficha do Cliente</h4>
        <p class="mb-0">Emitido: {{ now()->toDateString() }}</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dados do Cliente</div>
                <div class="card-body">
                    {{-- ID removido da ficha conforme solicitado --}}
                    <p><strong>Nome / Razão social:</strong> {{ $cliente->nome }}</p>
                    <p><strong>BI / NIF:</strong> {{ $cliente->bi }}</p>
                    <p><strong>Contacto (WhatsApp):</strong> {{ $cliente->contato }}</p>
                    <p><strong>Email:</strong> {{ $cliente->email }}</p>
                    <p><strong>Estado:</strong> {{ $cliente->estado ?? '—' }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Observações</div>
                <div class="card-body">
                    <p>{!! nl2br(e($cliente->observacoes ?? '')) !!}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Equipamentos Associados</div>
                <div class="card-body p-0">
                    @if((isset($cliente->equipamentos) && $cliente->equipamentos->count()) || (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count()))
                    <div class="estoque-tabela-moderna">
                        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
                            <thead>
                                <tr>
                                    <th style="text-align:center;vertical-align:middle;">Nº</th>
                                    <th style="text-align:center;vertical-align:middle;">Nome</th>
                                    <th style="text-align:center;vertical-align:middle;">Modelo</th>
                                    <th style="text-align:center;vertical-align:middle;">Série</th>
                                    <th style="text-align:center;vertical-align:middle;">Quantidade</th>
                                    <th style="text-align:center;vertical-align:middle;">Morada / Referência</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cliente->equipamentos ?? [] as $eq)
                                    <tr>
                                        <td style="text-align:center;vertical-align:middle;">{{ $eq->id }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $eq->nome ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $eq->modelo ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $eq->numero_serie ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $eq->quantidade ?? '1' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                                    </tr>
                                @endforeach
                                @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                                    @php $est = $vinc->equipamento; @endphp
                                    <tr>
                                        <td style="text-align:center;vertical-align:middle;">{{ $vinc->id }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->nome ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->modelo ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $est->numero_serie ?? '-' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $vinc->quantidade ?? '1' }}</td>
                                        <td style="text-align:center;vertical-align:middle;">{{ $vinc->morada ?? '—' }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <style>
                        .estoque-tabela-moderna {
                            background: #fff;
                            border-radius: 16px;
                            box-shadow: 0 2px 8px #0001;
                            padding: 18px 18px 8px 18px;
                            margin-top: 18px;
                            overflow-x: auto;
                        }
                        .tabela-estoque-moderna {
                            width: 100%;
                            min-width: 640px;
                            font-size: 1.07em;
                            border-collapse: collapse;
                            background: #fff;
                            border-radius: 8px;
                            overflow: hidden;
                        }
                        .tabela-estoque-moderna th,
                        .tabela-estoque-moderna td {
                            padding: 8px 6px;
                        }
                        .tabela-estoque-moderna th {
                            background: #fffbe7;
                            color: #f7b500;
                            font-weight: bold;
                            font-size: 1.09em;
                            border-bottom: 2px solid #ffe6a0;
                            padding: 14px 12px;
                        }
                        .tabela-estoque-moderna td {
                            background: #fff;
                            color: #222;
                            font-size: 1em;
                            padding: 13px 12px;
                        }
                        .tabela-estoque-moderna tr {
                            border-bottom: 1px solid #f3e6b0;
                        }
                        @media (max-width: 900px) {
                            .tabela-estoque-moderna {
                                min-width: 520px;
                                font-size: 0.98em;
                            }
                            /* Reduce padding on very small screens */
                            @media (max-width: 640px) {
                                .tabela-estoque-moderna th,
                                .tabela-estoque-moderna td {
                                    padding: 6px 6px;
                                    font-size: 0.95em;
                                }
                                .tabela-estoque-moderna { min-width: 480px; }
                            }
                        }
                    </style>
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
        btnCompensar.onclick = () => { modal.style.display = 'flex'; document.getElementById('dias_compensados').focus(); };
        fechar.onclick = () => { modal.style.display = 'none'; };
        modal.onclick = (e) => { if(e.target === modal) modal.style.display = 'none'; };
    }
});
</script>
@endpush
