@extends('layouts.app')

@section('content')
<div style="max-width:500px;margin:40px auto;background:#fffbe7;border-radius:16px;box-shadow:0 2px 8px #0001;padding:32px 32px 24px 32px;">
    <h2 style="color:#f7b500;text-align:center;margin-bottom:24px;">Comprovativo de Pagamento</h2>
    <p><strong>Cliente:</strong> {{ $cobranca->cliente->nome ?? '-' }}</p>
    <p><strong>Descrição:</strong> {{ $cobranca->descricao }}</p>
    <p><strong>Valor Pago:</strong> Kz {{ number_format($cobranca->valor, 2, ',', '.') }}</p>
    <p><strong>Data de Vencimento:</strong> {{ $cobranca->data_vencimento }}</p>
    <p><strong>Data de Pagamento:</strong> {{ $cobranca->data_pagamento ?? '-' }}</p>
    <p><strong>Status:</strong> 
        @if($cobranca->status === 'pago')
            <span style="color:#4caf50;font-weight:bold;">Pago</span>
        @elseif($cobranca->status === 'atrasado')
            <span style="color:#e53935;font-weight:bold;">Atrasado</span>
        @else
            <span style="color:#ffb800;font-weight:bold;">Pendente</span>
        @endif
    </p>
    <hr>
    <p style="font-size:0.95em;color:#888;text-align:center;">Gerado em {{ date('d/m/Y H:i') }}<br>LuandaWiFi</p>
</div>
@endsection
