@php
    // Simple PDF-specific layout for client ficha
    // Prefer embedded logoData (set by CLI command) to avoid remote fetch issues
    $logo = $logoData ?? asset('img/logo2.jpeg');
@endphp
<!doctype html>
<html>
    <style>
        /* PDF-friendly layout: clearer tables, spacing and print-safe rules */
        html, body { margin:0; padding:18px; font-family: Arial, Helvetica, sans-serif; color:#222; font-size:13px; line-height:1.35; }
        .header { text-align:center; margin-bottom:10px; }
        .header img, .ficha-logo { max-width:80px; width:auto; height:auto; display:block; margin:0 auto 6px auto; }
        .title { font-size:16px; font-weight:700; margin-bottom:6px; }
        .small { font-size:10px; color:#666; }
        .section { margin-bottom:14px; }
        .section-title { font-weight:700; margin:10px 0 6px; }
        .label { font-weight:700; display:inline-block; width:120px; vertical-align:top; }
        .value { display:inline-block; max-width:420px; }

        /* Tables: clear spacing and readable columns for PDF */
        table { width:100%; border-collapse:separate; border-spacing:0; margin-top:6px; font-size:12px; table-layout:fixed; }
        th, td { padding:8px 10px; border:1px solid #e7e7e7; vertical-align:top; word-wrap:break-word; overflow-wrap:break-word; }
        thead th { background:#f6f6f6; font-weight:700; text-align:left; }
        tbody tr:nth-child(odd) td { background: #fff; }

        /* Column widths for PDF */
        .col-id { width:6%; }
        .col-nome { width:28%; }
        .col-modelo { width:16%; }
        .col-serie { width:15%; }
        .col-quant { width:8%; text-align:center; }
        .col-morada { width:27%; }
        /* Badges for PDF */
        .badge { display:inline-block; padding:3px 7px; border-radius:6px; color:#fff; font-size:11px; }
        .badge.plan { background:#ffc107; color:#222; }
        .badge.cobranca-pago { background:#28a745; }
        .badge.cobranca-pendente { background:#ffc107; color:#222; }

        /* Small helpers */
        .muted { color:#666; font-size:11px; }
        footer { margin-top:16px; font-size:11px; color:#999; text-align:center; }

        /* Avoid page-break inside table rows */
        tr { page-break-inside: avoid; }
        thead { display:table-header-group; }
    </style>
    </style>
</head>
<body>
    <div class="header">
        <img class="ficha-logo" src="{{ $logo }}" alt="logo">
        <div class="title">Ficha do Cliente</div>
        <div class="small">Emitido: {{ now()->toDateString() }}</div>
    </div>

    <div class="card">
        <div class="section">
            {{-- ID removido por solicitação do usuário --}}
            <div><span class="label">Nome / Razão social:</span><span class="value">{{ $cliente->nome }}</span></div>
            <div><span class="label">BI / NIF:</span><span class="value">{{ $cliente->bi ?? '-' }}</span></div>
            <div><span class="label">Contacto (WhatsApp):</span><span class="value">{{ $cliente->contato ?? '-' }}</span></div>
            <div><span class="label">Email:</span><span class="value">{{ $cliente->email ?? '-' }}</span></div>
        </div>

        <div class="section">
            <strong>Planos Contratados</strong>
            @if(isset($cliente->planos) && $cliente->planos->count())
                <table>
                    <thead>
                        <tr><th>Nº</th><th>Plano</th><th>Ativação</th><th>Ciclo (dias)</th><th>Dias Compensados</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->planos as $pl)
                        <tr>
                            <td>{{ $pl->id }}</td>
                            <td>{{ $pl->nome }}</td>
                            <td>{{ $pl->data_ativacao ? \Carbon\Carbon::parse($pl->data_ativacao)->format('d/m/Y') : 'Sem data' }}</td>
                            <td>{{ $pl->ciclo ?? '-' }}</td>
                            <td>
                                @php
                                    // Dias compensados = ciclo atual - ciclo original (se disponível)
                                    $diasComp = null;
                                    if(isset($pl->ciclo_original)) {
                                        $diasComp = $pl->ciclo - $pl->ciclo_original;
                                    } else {
                                        $diasComp = $pl->dias_compensados ?? null;
                                    }
                                @endphp
                                {{ ($diasComp && $diasComp > 0) ? $diasComp : '-' }}
                            </td>
                            <td><span class="badge plan">{{ $pl->estado ?? '-' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="small">Nenhum plano contratado.</div>
            @endif
        </div>

        <div class="section">
            <strong>Equipamentos Associados</strong>
            @if((isset($cliente->equipamentos) && $cliente->equipamentos->count()) || (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count()))
                <table>
                    <thead>
                        <tr>
                            <th class="col-id">Nº</th>
                            <th class="col-nome">Nome</th>
                            <th class="col-modelo">Modelo</th>
                            <th class="col-serie">Série</th>
                            <th class="col-quant">Quantidade</th>
                            <th class="col-morada">Morada / Referência</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->equipamentos ?? [] as $eq)
                        <tr>
                            <td class="col-id">{{ $eq->id }}</td>
                            <td class="col-nome">{{ $eq->nome ?? '-' }}</td>
                            <td class="col-modelo">{{ $eq->modelo ?? '-' }}</td>
                            <td class="col-serie">{{ $eq->numero_serie ?? '-' }}</td>
                            <td class="col-quant">{{ $eq->quantidade ?? '1' }}</td>
                            <td class="col-morada">{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                        </tr>
                        @endforeach
                        @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                            @php $est = $vinc->equipamento; @endphp
                            <tr>
                                <td class="col-id">{{ $vinc->id }}</td>
                                <td class="col-nome">{{ $est->nome ?? '-' }}</td>
                                <td class="col-modelo">{{ $est->modelo ?? '-' }}</td>
                                <td class="col-serie">{{ $est->numero_serie ?? '-' }}</td>
                                <td class="col-quant">{{ $vinc->quantidade ?? '1' }}</td>
                                <td class="col-morada">{{ $vinc->morada ?? '—' }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="small">Nenhum equipamento cadastrado para este cliente.</div>
            @endif
        </div>

        <div class="section">
            <strong>Cobranças (pendentes / recentes)</strong>
            @if($cliente->cobrancas && $cliente->cobrancas->count())
                <table>
                    <thead>
                        <tr><th>Nº</th><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Status</th></tr>
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
                                    <span class="badge cobranca-pago">Pago</span>
                                @elseif(isset($c->status) && $c->status === 'atrasado')
                                    <span class="badge" style="background:#dc3545;">Atrasado</span>
                                @else
                                    <span class="badge cobranca-pendente">Pendente</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="small">Sem cobranças associadas.</div>
            @endif
        </div>

        <footer>Ficha gerada por SGA-MR.TEXAS • {{ now()->format('Y') }}</footer>
    </div>
</body>
</html>
