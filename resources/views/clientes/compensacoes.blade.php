@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Histórico de Compensações — {{ $cliente->nome }}</h1>
    <p>
        <a href="{{ route('clientes.show', $cliente->id) }}" class="btn btn-secondary">Voltar</a>
    </p>

    @if($compensacoes->isEmpty())
        <div class="alert alert-info">Nenhuma compensação encontrada para este cliente.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plano</th>
                        <th>Dias</th>
                        <th>Anter.</th>
                        <th>Novo</th>
                        <th>Usuário</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compensacoes as $c)
                        <tr>
                            <td>{{ $c->id }}</td>
                            <td>{{ $planoMap[$c->plano_id]->nome ?? ('Plano #' . $c->plano_id) }}</td>
                            <td>{{ $c->dias_compensados }}</td>
                            <td>{{ $c->anterior }}</td>
                            <td>{{ $c->novo }}</td>
                            <td>{{ $users[$c->user_id]->name ?? ($c->user_id ? 'Usuário #' . $c->user_id : '-') }}</td>
                            <td>{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
