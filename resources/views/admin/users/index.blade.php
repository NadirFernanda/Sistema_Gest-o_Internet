@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Usuários</div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" class="form-inline" style="margin-bottom:12px;">
                <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou e-mail" value="{{ request('q') }}">
                <button class="btn btn-default" type="submit">Buscar</button>
                @can('users.create')
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="margin-left:8px;">Criar usuário</a>
                @endcan
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Papeis</th>
                            <th>Criado</th>
                            <th style="text-align:center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->roles->pluck('name')->join(', ') }}</td>
                                <td>{{ $u->created_at->format('Y-m-d') }}</td>
                                <td style="text-align:center;">
                                    @can('users.edit')
                                        <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-ghost">Editar</a>
                                    @endcan
                                    @can('users.delete')
                                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display:inline-block;margin-left:6px;" onsubmit="return confirm('Confirma exclusão deste usuário?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Apagar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>
</div>
@endsection
