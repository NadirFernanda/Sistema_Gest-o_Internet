@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Equipamento para {{ $cliente->nome }}</h2>
    <form action="{{ route('equipamentos.store', $cliente->id) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome do Equipamento</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="mb-3">
            <label for="morada" class="form-label">Morada</label>
            <input type="text" class="form-control" id="morada" name="morada" required>
        </div>
        <div class="mb-3">
            <label for="ponto_referencia" class="form-label">Ponto de Referência</label>
            <input type="text" class="form-control" id="ponto_referencia" name="ponto_referencia">
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>

    <hr>
    <h3>Equipamentos já cadastrados para {{ $cliente->nome }}</h3>
    @if($cliente->equipamentos->count())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Morada</th>
                    <th>Ponto de Referência</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cliente->equipamentos as $equipamento)
                    <tr>
                        <td>{{ $equipamento->nome }}</td>
                        <td>{{ $equipamento->morada }}</td>
                        <td>{{ $equipamento->ponto_referencia }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum equipamento cadastrado ainda para este cliente.</p>
    @endif
</div>
@endsection
