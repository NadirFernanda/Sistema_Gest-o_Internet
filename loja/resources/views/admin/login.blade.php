@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Acesso ao painel administrativo">
  <div class="container" style="max-width:460px;margin:4rem auto;">
    <h2 style="margin-bottom:.25rem;">Painel Administrativo</h2>
    <p class="lead" style="margin-bottom:1.5rem;">Introduza a palavra-passe de acesso.</p>

    @if(session('error'))
      <div class="alert" role="alert"
           style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
        {{ session('error') }}
      </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}" autocomplete="off"
          style="background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1.75rem 2rem;">
      @csrf
      <div class="form-row" style="margin-bottom:1.25rem;">
        <label for="password" style="display:block;font-weight:600;margin-bottom:.4rem;">Palavra-passe</label>
        <input
          id="password"
          type="password"
          name="password"
          class="newsletter-input"
          style="width:100%;box-sizing:border-box;"
          autofocus
          required
          autocomplete="current-password"
        >
      </div>
      <div class="form-actions" style="margin-top:0;">
        <button type="submit" class="btn-primary" style="width:100%;">Entrar</button>
      </div>
    </form>
  </div>
</section>
@endsection
