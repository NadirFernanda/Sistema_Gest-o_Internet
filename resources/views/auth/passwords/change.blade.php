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
  padding:2rem;
}
.change-card{width:100%;max-width:720px;}
.form-actions{display:flex;gap:1rem;align-items:center;margin-top:1rem}
@media (max-width:576px){.change-card{padding:1rem;margin:0 0.5rem}}
</style>

<div class="change-password-wrapper">
  <div class="change-card">
    <div class="change-card-header">
      <a href="{{ url()->previous() }}" onclick="event.preventDefault(); history.back();" class="btn-back-circle btn-ghost" title="Voltar" aria-label="Voltar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
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
      </div>
    </form>
  </div>
</div>

@endsection
