@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
  @include('layouts.partials.clientes-hero', [
    'title' => 'Alterar senha',
    'subtitle' => ''
  ])

<style>
.change-password-wrapper{
  min-height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
  flex-direction:column;
  padding:2rem;
}
.change-card{width:100%;max-width:720px;}
.form-actions{display:flex;gap:1rem;align-items:center;margin-top:1rem}
@media (max-width:576px){.change-card{padding:1rem;margin:0 0.5rem}}
</style>

<div class="change-password-wrapper">
  <div class="back-button-wrapper" style="text-align:center; margin-bottom:12px;">
    <a href="javascript:history.back()" class="btn" title="Voltar" aria-label="Voltar" style="display:inline-flex; margin:0 auto; padding:10px 14px; border-radius:8px; background:#f7b500; color:#fff; box-shadow:0 6px 18px rgba(0,0,0,.08);">Voltar</a>
  </div>
  <div class="change-card">
    <div class="change-card-header" style="text-align:center;">
      <h2 style="margin-top:6px;">Alterar senha</h2>
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
      </div>
    </form>
  </div>
</div>

@endsection
