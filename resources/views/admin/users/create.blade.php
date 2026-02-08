@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mx-auto create-card" style="max-width:720px">
            <div class="card-accent"></div>
            <div class="card-header">Criar Usuário</div>
            <div class="card-subtitle">Adicione um novo usuário e atribua um papel</div>
            <div class="card-top-actions">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Voltar</a>
            </div>
            <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form id="create-user-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nome</label>
                        <input class="form-control" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">E-mail (local-part ou completo)</label>
                        <input class="form-control" name="email" value="{{ old('email') }}" placeholder="ex: joao ou joao@" required>
                        <small class="text-muted">Se apenas 'joao', anexaremos <strong>@sgmrtexas.angolawifi.ao</strong></small>
                        @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Senha</label>
                        <input type="password" class="form-control" name="password" required>
                        @error('password')<div class="text-danger">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirmar senha</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">Papel</label>
                        <select name="role" class="form-control">
                            <option value="">-- Selecionar --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
