@php
    // Simple PDF-specific layout for client ficha
    $logo = asset('img/logo2.jpeg');
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Ficha do Cliente - {{ $cliente->nome }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
        .header { text-align: center; margin-bottom: 12px; }
        .header img { width: 120px; }
        .card { background: #fff; border-radius: 6px; padding: 14px; box-shadow: none; }
        .title { color: #e09b00; font-size: 20px; font-weight: 700; margin-top:8px; margin-bottom:14px; }
        .section { margin-bottom: 12px; }
        .label { font-weight:700; width:160px; display:inline-block; vertical-align:top; }
        .value { display:inline-block; max-width:380px; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        th, td { padding:6px 8px; border:1px solid #eee; font-size:12px; }
        thead th { background:#fff9e6; font-weight:700; }
        .small { font-size:11px; color:#666; }
        footer { margin-top:18px; font-size:11px; color:#999; text-align:center; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ $logo }}" alt="logo">
        <div class="title">Ficha do Cliente</div>
        <div class="small">Emitido: {{ now()->toDateString() }}</div>
    </div>

    <div class="card">
        <div class="section">
            <div><span class="label">ID:</span><span class="value">{{ $cliente->id }}</span></div>
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
                            <td>{{ optional($pl->data_ativacao)->toDateString() ?? '-' }}</td>
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
                        <tr><th>#</th><th>Nome / Modelo</th><th>Série</th><th>Quantidade / Morada</th></tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->equipamentos ?? [] as $eq)
                        <tr>
                            <td>{{ $eq->id }}</td>
                            <td>{{ $eq->nome ?? $eq->modelo ?? '-' }}</td>
                            <td>{{ $eq->numero_serie ?? '-' }}</td>
                            <td>{{ $eq->ponto_referencia ?? $eq->morada ?? '-' }}</td>
                        </tr>
                        @endforeach
                        @foreach($cliente->clienteEquipamentos ?? [] as $vinc)
                            @php $est = $vinc->equipamento; @endphp
                            <tr>
                                <td>{{ $vinc->id }}</td>
                                <td>{{ $est->nome ?? $est->modelo ?? 'Equipamento do estoque' }}</td>
                                <td>{{ $est->numero_serie ?? '-' }}</td>
                                <td>{{ $vinc->quantidade }}x • {{ $vinc->morada }}{{ $vinc->ponto_referencia ? ' (Ref: '.$vinc->ponto_referencia.')' : '' }}</td>
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
                            <td>{{ optional($c->data_vencimento)->toDateString() ?? '-' }}</td>
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
