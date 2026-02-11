<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovativo de Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #fffbe7; }
        .comprovante-box { border: 1px solid #f7b500; padding: 32px; border-radius: 16px; max-width: 500px; margin: auto; background: #fff; box-shadow: 0 2px 8px #0001; }
        h2 { color: #f7b500; text-align: center; margin-bottom: 24px; }
        .info { margin-bottom: 12px; }
        .label { font-weight: bold; }
        hr { margin: 24px 0; }
        .footer { text-align: center; font-size: 0.95em; color: #888; }
    </style>
</head>
<body>
    <div class="comprovante-box">
        <div style="text-align:center;margin-bottom:16px;">
            <img src="{{ public_path('img/logo2.jpeg') }}" alt="Logotipo" style="max-width:140px;max-height:80px;">
        </div>
        <h2>Comprovativo de Pagamento</h2>
        <div class="info"><span class="label">Cliente:</span> {{ $cobranca->cliente->nome ?? '-' }}</div>
        <div class="info"><span class="label">BI:</span> {{ $cobranca->cliente->bi ?? '-' }}</div>
        <div class="info"><span class="label">Email:</span> {{ $cobranca->cliente->email ?? '-' }}</div>
        <div class="info"><span class="label">Contacto:</span> {{ $cobranca->cliente->contato ?? '-' }}</div>
        <div class="info"><span class="label">Descrição:</span> {{ $cobranca->descricao }}</div>
        <div class="info"><span class="label">Valor Pago:</span> Kz {{ number_format($cobranca->valor, 2, ',', '.') }}</div>
        <div class="info"><span class="label">Data de Vencimento:</span> {{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : 'Sem data' }}</div>
        <div class="info"><span class="label">Data de Pagamento:</span> {{ $cobranca->data_pagamento ?? '-' }}</div>
        <div class="info"><span class="label">Status:</span> 
            @if($cobranca->status === 'pago')
                <span style="color:#4caf50;font-weight:bold;">Pago</span>
            @elseif($cobranca->status === 'atrasado')
                <span style="color:#e53935;font-weight:bold;">Atrasado</span>
            @else
                <span style="color:#f7b500;font-weight:bold;">Pendente</span>
            @endif
        </div>
        <hr>
        <div class="footer">Gerado em {{ date('d/m/Y H:i') }}<br>LuandaWiFi</div>
    </div>
</body>
</html>
