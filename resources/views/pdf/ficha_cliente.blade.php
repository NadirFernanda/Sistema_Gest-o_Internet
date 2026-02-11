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
        table { width:100%; border-collapse:separate; border-spacing:0; margin-top:6px; font-size:12px; }
        th, td { padding:8px 10px; border:1px solid #e7e7e7; vertical-align:top; }
        thead th { background:#f6f6f6; font-weight:700; text-align:left; }
        tbody tr:nth-child(odd) td { background: #fff; }

        /* Small helpers */
        .muted { color:#666; font-size:11px; }
        footer { margin-top:16px; font-size:11px; color:#999; text-align:center; }

        /* Avoid page-break inside table rows */
        tr { page-break-inside: avoid; }
        thead { display:table-header-group; }
    </style>
        thead th { background:#f6f6f6; font-weight:700; }
        footer { margin-top:12px; font-size:10px; color:#999; text-align:center; }
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
                        <tr><th>#</th><th>Plano</th><th>Ativação</th><th>Ciclo (dias)</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->planos as $pl)
                        <tr>
                            <td>{{ $pl->id }}</td>
                            <td>{{ $pl->nome }}</td>
                            <td>{{ $pl->data_ativacao ? \Carbon\Carbon::parse($pl->data_ativacao)->format('d/m/Y') : 'Sem data' }}</td>
                            <td>{{ $pl->ciclo ?? '-' }}</td>
                            <td>{{ $pl->estado ?? '-' }}</td>
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
                            <th>#</th>
                            <th>Nome</th>
                            <th>Modelo</th>
                            <th>Série</th>
                            <th>Quantidade</th>
                            <th>Morada / Referência</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->equipamentos ?? [] as $eq)
                        <tr>
                            <td>{{ $eq->id }}</td>
                            <td>{{ $eq->nome ?? '-' }}</td>
                            <td>{{ $eq->modelo ?? '-' }}</td>
                            <td>{{ $eq->numero_serie ?? '-' }}</td>
                            <td>{{ $eq->quantidade ?? '1' }}</td>
                            <td>{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                        </tr>
                        @endforeach
                        @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                            @php $est = $vinc->equipamento; @endphp
                            <tr>
                                <td>{{ $vinc->id }}</td>
                                <td>{{ $est->nome ?? '-' }}</td>
                                <td>{{ $est->modelo ?? '-' }}</td>
                                <td>{{ $est->numero_serie ?? '-' }}</td>
                                <td>{{ $vinc->quantidade ?? '1' }}</td>
                                <td>{{ $vinc->morada ?? '—' }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
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
                        <tr><th>#</th><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->cobrancas as $c)
                        <tr>
                            <td>{{ $c->id }}</td>
                            <td>{{ $c->descricao ?? '-' }}</td>
                            <td>{{ number_format($c->valor, 2, ',', '.') }} Kz</td>
                            <td>{{ $c->data_vencimento ? \Carbon\Carbon::parse($c->data_vencimento)->format('d/m/Y') : 'Sem data' }}</td>
                            <td>{{ $c->estado ?? '-' }}</td>
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
