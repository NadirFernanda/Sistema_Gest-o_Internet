@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes da Cobrança</h1>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">{{ $cobranca->descricao }}</h5>
            <p><strong>Cliente:</strong> {{ $cobranca->cliente->nome ?? '-' }}</p>
            <p><strong>Valor:</strong> Kz {{ number_format($cobranca->valor, 2, ',', '.') }}</p>
            <p><strong>Vencimento:</strong> {{ $cobranca->data_vencimento ? \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') : 'Sem data' }}</p>
            <p><strong>Pagamento:</strong> {{ $cobranca->data_pagamento ?? '-' }}</p>
            <p><strong>Status:</strong>
                @if($cobranca->status === 'pago')
                    <span class="badge bg-success">Pago</span>
                @elseif($cobranca->status === 'atrasado')
                    <span class="badge bg-danger">Atrasado</span>
                @else
                    <span class="badge bg-warning text-dark">Pendente</span>
                @endif
            </p>
        </div>
    </div>
    <a href="{{ route('cobrancas.comprovante', $cobranca->id) }}" class="btn btn-primary mt-3" target="_blank">Gerar Comprovante PDF</a>
    <a href="{{ route('cobrancas.index') }}" class="btn btn-secondary mt-3">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px;">
            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Voltar
    </a>
</div>
@endsection
