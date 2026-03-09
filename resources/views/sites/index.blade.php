@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sites</h1>
    <a href="{{ route('sites.create') }}" class="btn btn-primary mb-3">Novo Site</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Localização</th>
                <th>Status</th>
                <th>Capacidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sites as $site)
            <tr>
                <td>{{ $site->id }}</td>
                <td>{{ $site->nome }}</td>
                <td>{{ $site->localizacao }}</td>
                <td>{{ $site->status }}</td>
                <td>{{ $site->capacidade }}</td>
                <td>
                    <a href="{{ route('sites.show', $site) }}" class="btn btn-sm btn-info">Ver</a>
                    <a href="{{ route('sites.edit', $site) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('sites.destroy', $site) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apagar este site?')">Apagar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
