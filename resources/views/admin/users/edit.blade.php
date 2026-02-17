@extends('layouts.app')

@section('content')
<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Editar Usuário',
        'subtitle' => '',
        'stackLeft' => true,
    ])

    <div style="margin-top:8px;">
        <a href="{{ route('admin.users.index') }}" class="btn-back-circle btn-ghost" title="Voltar" aria-label="Voltar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
    </div>

    <div class="container" style="max-width:720px;margin:0 auto;">
        <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="form-modern">
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
                <select name="role" class="form-control select">
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
@endsection
