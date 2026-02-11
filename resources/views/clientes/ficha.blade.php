@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4>Ficha do Cliente</h4>
                <p class="mb-0">Emitido: {{ now()->toDateString() }}</p>
            </div>
            <div class="text-end">
                <a href="{{ route('clientes.ficha.pdf', $cliente->id) }}" class="btn btn-sm btn-secondary">Download PDF</a>
                <form action="{{ route('clientes.ficha.send', $cliente->id) }}" method="post" style="display:inline;">
                    @csrf
                    <button class="btn btn-sm btn-primary">Enviar por e-mail</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Dados do Cliente</div>
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $cliente->id }}</p>
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
                    @if($cliente->equipamentos && $cliente->equipamentos->count())
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome/Modelo</th>
                                <th>Série</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->equipamentos as $eq)
                            <tr>
                                <td>{{ $eq->id }}</td>
                                <td>{{ $eq->nome ?? $eq->modelo ?? '-' }}</td>
                                <td>{{ $eq->numero_serie ?? '-' }}</td>
                                <td>{{ $eq->estado ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="p-3 mb-0">Nenhum equipamento cadastrado para este cliente.</p>
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
                                <th>#</th>
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
                                <td>{{ optional($c->data_vencimento)->toDateString() }}</td>
                                <td>{{ $c->estado ?? '-' }}</td>
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
