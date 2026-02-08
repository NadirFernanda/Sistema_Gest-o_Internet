@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mx-auto create-card" style="max-width:640px">
        <div class="card-header">Criar Usuário</div>
        <div class="card-top-actions">
            <button type="submit" form="create-user-form" class="btn btn-primary btn-submit-top">Criar</button>
        </div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form id="create-user-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input class="form-control" name="name" value="{{ old('name') }}" required>
                    @error('name')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">E-mail (use apenas o local-part ou e‑mail completo)</label>
                    <div class="input-group">
                        <input class="form-control" name="email" value="{{ old('email') }}" placeholder="ex: joao ou joao@" required>
                    </div>
                    <small class="text-muted">Se você digitar apenas 'joao', o domínio <strong>@sgmrtexas.angolawifi.ao</strong> será anexado automaticamente.</small>
                    @error('email')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input type="password" class="form-control" name="password" required>
                    @error('password')<div class="text-danger">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirmar senha</label>
                    <input type="password" class="form-control" name="password_confirmation" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Papel</label>
                    <select name="role" class="form-control">
                        <option value="">-- Selecionar --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
