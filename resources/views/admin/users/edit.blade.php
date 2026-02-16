@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mx-auto" style="max-width:720px">
        <div class="card-header">Editar Usuário</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nome</label>
                    <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group">
                    <label>E-mail</label>
                    <input name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="form-group">
                    <label>Senha (deixe em branco para manter)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-group">
                    <label>Confirmar senha</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label>Papel</label>
                    <select name="role" class="form-control">
                        <option value="">-- Nenhum --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" {{ in_array($r, $user->roles->pluck('name')->toArray()) ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Salvar</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
