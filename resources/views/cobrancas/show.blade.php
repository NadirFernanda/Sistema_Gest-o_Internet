
@extends('layouts.app')

 @section('content')
<div class="container">
    <h1>Detalhes da Cobrança</h1>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">{{ $cobranca->descricao }}</h5>
            <p><strong>Cliente:</strong> {{ $cobranca->cliente_nome }}</p>
            <p><strong>Valor:</strong> Kz {{ $cobranca->valor_formatado }}</p>
            <p><strong>Vencimento:</strong> {{ $cobranca->data_vencimento_formatada }}</p>
            <p><strong>Pagamento:</strong> {{ $cobranca->data_pagamento_formatada }}</p>
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
</div>
@endsection
