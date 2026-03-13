@extends('layouts.app')

@section('content')
<style>
.adm-login-page {
  min-height: 70vh;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}
.adm-login-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(79,70,229,.08), 0 1px 4px rgba(0,0,0,.06);
  overflow: hidden;
}
.adm-login-header {
  background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
  padding: 2rem 2rem 1.75rem;
  text-align: center;
}
.adm-login-header .adm-login-logo {
  width: 52px; height: 52px;
  background: rgba(255,255,255,.15);
  border-radius: 14px;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 1.6rem;
  margin-bottom: .85rem;
}
.adm-login-header h1 {
  font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 .25rem;
  letter-spacing: -.02em;
}
.adm-login-header p {
  font-size: .82rem; color: rgba(255,255,255,.7); margin: 0;
}
.adm-login-body { padding: 1.75rem 2rem 2rem; }
.adm-login-error {
  background: #fef2f2;
  border: 1px solid #fca5a5;
  border-left: 4px solid #ef4444;
  color: #991b1b;
  padding: .7rem .9rem;
  border-radius: 8px;
  font-size: .84rem;
  margin-bottom: 1.25rem;
  display: flex; align-items: center; gap: .5rem;
}
.adm-login-label {
  display: block;
  font-size: .78rem;
  font-weight: 700;
  color: #374151;
  letter-spacing: .03em;
  text-transform: uppercase;
  margin-bottom: .35rem;
}
.adm-login-input {
  width: 100%;
  box-sizing: border-box;
  padding: .65rem .9rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 9px;
  font-size: .9rem;
  color: #1e293b;
  background: #f8fafc;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
  font-family: inherit;
}
.adm-login-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,.15);
  background: #fff;
}
.adm-login-submit {
  width: 100%;
  margin-top: 1.25rem;
  padding: .75rem;
  background: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 9px;
  font-size: .95rem;
  font-weight: 700;
  cursor: pointer;
  letter-spacing: .01em;
  transition: background .15s, transform .1s;
  font-family: inherit;
}
.adm-login-submit:hover  { background: #4338ca; }
.adm-login-submit:active { transform: scale(.99); }
.adm-login-foot {
  padding: .85rem 1.5rem;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  text-align: center;
  font-size: .75rem;
  color: #94a3b8;
}
</style>

<div class="adm-login-page">
  <div class="adm-login-card">
    <div class="adm-login-header">
      <div class="adm-login-logo">🔐</div>
      <h1>Painel Administrativo</h1>
      <p>AngolaWiFi · Acesso restrito</p>
    </div>

    <div class="adm-login-body">
      @if(session('error'))
        <div class="adm-login-error">
          <span>⚠</span> {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('admin.login.submit') }}" autocomplete="off">
        @csrf
        <label for="password" class="adm-login-label">Palavra-passe</label>
        <input
          id="password"
          type="password"
          name="password"
          class="adm-login-input"
          placeholder="••••••••"
          autofocus
          required
          autocomplete="current-password"
        >
        <button type="submit" class="adm-login-submit">Entrar →</button>
      </form>
    </div>

    <div class="adm-login-foot">
      Acesso autorizado apenas para administradores.
    </div>
  </div>
</div>
@endsection
