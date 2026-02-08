@extends('layouts.app')

@section('content')
<div class="change-password-wrapper">
  <div class="change-card">
    <div class="change-card-header">
      <a href="{{ url()->previous() }}" class="btn btn-back">← Voltar</a>
      <h2>Alterar senha</h2>
    </div>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="change-form">
      @csrf

      <label class="form-label">Senha atual</label>
      <input type="password" name="current_password" class="form-control" required>
      @error('current_password')<div class="text-danger">{{ $message }}</div>@enderror

      <label class="form-label">Nova senha</label>
      <input type="password" name="password" class="form-control" required>
      @error('password')<div class="text-danger">{{ $message }}</div>@enderror

      <label class="form-label">Confirme nova senha</label>
      <input type="password" name="password_confirmation" class="form-control" required>

      <div class="form-actions">
        <button class="btn" type="submit">Alterar senha</button>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost">Ir ao painel</a>
      </div>
    </form>
  </div>
</div>

@endsection
