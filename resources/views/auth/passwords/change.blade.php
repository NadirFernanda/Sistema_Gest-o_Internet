@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Alterar senha</h3>
  @if(session('status')) <div class="alert alert-success">{{ session('status') }}</div> @endif
  <form method="POST" action="{{ route('password.update') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Senha atual</label>
      <input type="password" name="current_password" class="form-control" required>
      @error('current_password')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
      <label class="form-label">Nova senha</label>
      <input type="password" name="password" class="form-control" required>
      @error('password')<div class="text-danger">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
      <label class="form-label">Confirme nova senha</label>
      <input type="password" name="password_confirmation" class="form-control" required>
    </div>
    <button class="btn btn-primary" type="submit">Alterar senha</button>
  </form>
</div>
@endsection
