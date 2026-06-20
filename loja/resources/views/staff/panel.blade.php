@extends('layouts.app')

@push('styles')
<style>
/* ── Painel Equipa ── */
.st-page {
  min-height: 80vh;
  background: #f8fafc;
  padding: 2.5rem 1rem 6rem;
}
.st-wrap { max-width: 860px; margin: 0 auto; }

/* Topbar */
.st-topbar {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.06);
  padding: 1.1rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .75rem;
  margin-bottom: 1.5rem;
}
.st-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg,#f7b500,#d97706);
  color: #1a202c;
  font-size: 1.1rem;
  font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.st-topbar-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
.st-topbar-sub  { font-size: .82rem; color: #64748b; margin-top: .1rem; }

/* Login card */
.st-login-wrap { max-width: 440px; margin: 3rem auto 0; }
.st-login-card {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 4px 32px rgba(0,0,0,.08);
  padding: 2.5rem 2.25rem 2rem;
}
.st-brand { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.75rem; }
.st-brand img { width: 44px; height: 44px; border-radius: .5rem; object-fit: cover; }
.st-brand-text { font-size: 1rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
.st-brand-text span { display: block; font-size: .78rem; font-weight: 500; color: #64748b; }
.st-login-card h2 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin-bottom: .3rem; }
.st-login-card p.st-sub { font-size: .9rem; color: #64748b; margin-bottom: 1.5rem; }
.st-field { margin-bottom: 1rem; }
.st-field label { display: block; font-size: .85rem; font-weight: 600; color: #374151; margin-bottom: .35rem; }
.st-field input {
  width: 100%; padding: .7rem 1rem;
  border: 1.5px solid #e2e8f0; border-radius: .6rem;
  font-size: .95rem; color: #0f172a; background: #f8fafc;
  transition: border-color .2s; box-sizing: border-box;
}
.st-field input:focus { outline: none; border-color: #f7b500; box-shadow: 0 0 0 3px rgba(247,181,0,.15); background: #fff; }
.st-field-error { color: #dc2626; font-size: .83rem; margin-top: .3rem; }
.st-btn-login {
  display: block; width: 100%; padding: .8rem 1.5rem;
  background: #f7b500; color: #1a202c;
  border: none; border-radius: .65rem;
  font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .2s;
}
.st-btn-login:hover { background: #e0a800; }

/* Stats */
.st-stats {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: .85rem;
  margin-bottom: 1.5rem;
}
.st-stat {
  background: #fff;
  border-radius: .9rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.1rem 1.2rem;
  border-top: 3px solid #f7b500;
}
.st-stat.green { border-color: #16a34a; }
.st-stat.purple { border-color: #7c3aed; }
.st-stat-val { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: .2rem; }
.st-stat-lbl { font-size: .74rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
.st-stat-sub { font-size: .72rem; color: #9aa5b4; margin-top: .3rem; }

/* Sell panel */
.st-panel {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.5rem;
  margin-bottom: 1.25rem;
}
.st-panel-title {
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
  padding-bottom: .65rem;
  margin-bottom: 1rem;
  border-bottom: 1.5px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .45rem;
}

/* Plan grid */
.st-plan-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}
.st-plan-card {
  border: 1.5px solid #e2e8f0;
  border-radius: .85rem;
  padding: 1.1rem;
  display: flex;
  flex-direction: column;
  gap: .6rem;
  transition: border-color .2s, box-shadow .2s;
}
.st-plan-card:hover { border-color: #f7b500; box-shadow: 0 3px 14px rgba(0,0,0,.08); }
.st-plan-card.out { opacity: .5; pointer-events: none; }
.st-plan-name { font-size: 1rem; font-weight: 800; color: #0f172a; }
.st-plan-price { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1; }
.st-plan-price span { font-size: .85rem; font-weight: 500; color: #64748b; }
.st-plan-stock { font-size: .8rem; font-weight: 600; }
.st-plan-stock.ok  { color: #16a34a; }
.st-plan-stock.out { color: #dc2626; }
.st-btn-sell {
  width: 100%;
  padding: .55rem .85rem;
  background: #f7b500;
  color: #1a202c;
  border: none; border-radius: .5rem;
  font-size: .88rem; font-weight: 700;
  cursor: pointer; transition: background .15s;
  margin-top: auto;
}
.st-btn-sell:hover { background: #e0a800; }

/* Alert */
.st-alert {
  border-radius: .75rem;
  padding: .9rem 1.1rem;
  margin-bottom: 1rem;
  font-size: .92rem;
  display: flex; align-items: flex-start; gap: .65rem;
}
.st-alert.success { background: #f0fdf4; border: 1.5px solid #86efac; color: #15803d; }
.st-alert.error   { background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c; }

/* History table */
.st-hist-wrap { overflow-x: auto; }
.st-hist-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .87rem;
  min-width: 460px;
}
.st-hist-table th {
  text-align: left; padding: .55rem .9rem;
  font-size: .7rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .05em;
  color: #9aa5b4; background: #f8fafc;
  border-bottom: 1.5px solid #e2e8f0;
}
.st-hist-table td { padding: .55rem .9rem; border-bottom: 1px solid #f4f6f9; color: #374151; }
.st-hist-table tbody tr:last-child td { border-bottom: none; }
.st-hist-table tbody tr:hover td { background: #fffdf5; }
.st-code-mono { font-family: monospace; font-size: .95rem; font-weight: 700; color: #0f172a; letter-spacing: .05em; }

/* Code reveal box */
.st-code-reveal {
  background: linear-gradient(135deg, #f7b500, #fbbf24);
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
  margin-bottom: 1.25rem;
}
.st-code-reveal p { margin: 0 0 .5rem; font-size: .85rem; font-weight: 600; color: #4a3a00; }
.st-code-big { font-family: monospace; font-size: 2rem; font-weight: 900; color: #1a1100; letter-spacing: .15em; }
.st-code-copy-btn {
  display: inline-flex; align-items: center; gap: .4rem;
  margin-top: .85rem; padding: .5rem 1.25rem;
  background: #1a202c; color: #f7b500;
  border: none; border-radius: .5rem;
  font-size: .88rem; font-weight: 700; cursor: pointer;
}

/* logout btn */
.st-logout-btn {
  padding: .5rem 1.1rem;
  border: 1.5px solid #fecaca;
  border-radius: .6rem;
  background: #fff1f2;
  font-size: .85rem; font-weight: 600; color: #dc2626;
  cursor: pointer;
}
.st-logout-btn:hover { background: #fecaca; }

@media (max-width: 580px) {
  .st-page { padding: 1.25rem .65rem 6rem; }
  .st-stats { grid-template-columns: 1fr 1fr; }
  .st-plan-grid { grid-template-columns: 1fr 1fr; }
  .st-topbar { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 420px) {
  .st-plan-grid { grid-template-columns: 1fr; }
  .st-stats { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="st-page">
<div class="st-wrap">

  {{-- ════ NÃO AUTENTICADO ════ --}}
  @if(!$staff)
    <div class="st-login-wrap">
      <div class="st-login-card">
        <div class="st-brand">
          <img src="{{ asset('img/logo2.jpeg') }}" alt="AngolaWiFi">
          <div class="st-brand-text">
            AngolaWiFi
            <span>Painel da Equipa de Revenda</span>
          </div>
        </div>

        @if($suspended ?? false)
          <div class="st-alert error" style="margin-bottom:1rem;">
            <span>🔒</span>
            <div>A sua conta está <strong>suspensa</strong>. Contacte o seu Agente Revendedor.</div>
          </div>
        @endif

        <h2>Entrar no painel</h2>
        <p class="st-sub">Acesso exclusivo para membros de equipa registados por um Agente Revendedor.</p>

        <form action="{{ route('staff.panel.login') }}" method="POST" novalidate>
          @csrf
          <div class="st-field">
            <label for="staff-phone">Número de telemóvel</label>
            <input id="staff-phone" name="phone" type="tel"
                   placeholder="9XXXXXXXX"
                   value="{{ old('phone') }}" required autocomplete="tel">
          </div>
          <div class="st-field">
            <label for="staff-pin">PIN (definido pelo seu agente)</label>
            <input id="staff-pin" name="pin" type="password"
                   placeholder="••••" maxlength="6" required autocomplete="off">
            @error('pin')
              <p class="st-field-error">{{ $message }}</p>
            @enderror
          </div>
          <button type="submit" class="st-btn-login">Entrar →</button>
        </form>
      </div>
    </div>

  {{-- ════ AUTENTICADO ════ --}}
  @else

    {{-- Topbar --}}
    <div class="st-topbar">
      <div style="display:flex;align-items:center;gap:.9rem;">
        <div class="st-avatar">{{ mb_strtoupper(mb_substr($staff->full_name, 0, 1)) }}</div>
        <div>
          <div class="st-topbar-name">{{ $staff->full_name }}</div>
          <div class="st-topbar-sub">Equipa de {{ $application->full_name }}</div>
        </div>
      </div>
      <form action="{{ route('staff.panel.logout') }}" method="POST" style="margin:0;">
        @csrf
        <button type="submit" class="st-logout-btn">Terminar sessão</button>
      </form>
    </div>

    {{-- Código vendido --}}
    @if(session('sold_code'))
      <div class="st-code-reveal">
        <p>✅ Voucher distribuído com sucesso! Entregue este código ao cliente:</p>
        <div class="st-code-big" id="sold-code-text">{{ session('sold_code') }}</div>
        <button class="st-code-copy-btn" onclick="navigator.clipboard.writeText('{{ session('sold_code') }}');this.textContent='✔ Copiado!'">
          📋 Copiar código
        </button>
      </div>
    @endif

    @if(session('sell_error'))
      <div class="st-alert error" style="margin-bottom:1rem;">
        <span>⚠️</span>
        <div>{{ session('sell_error') }}</div>
      </div>
    @endif

    {{-- Stats --}}
    <div class="st-stats">
      <div class="st-stat">
        <div class="st-stat-val">{{ number_format($myMonthlySold, 0, ',', '.') }}</div>
        <div class="st-stat-lbl">Vendidos este mês</div>
        <div class="st-stat-sub">vouchers distribuídos</div>
      </div>
      <div class="st-stat green">
        <div class="st-stat-val" style="color:#16a34a;">{{ number_format($myTotalAoa, 0, ',', '.') }}</div>
        <div class="st-stat-lbl">Receita total (Kz)</div>
        <div class="st-stat-sub">preço público acumulado</div>
      </div>
      <div class="st-stat purple">
        <div class="st-stat-val" style="color:#7c3aed;">{{ number_format($myTotalSold, 0, ',', '.') }}</div>
        <div class="st-stat-lbl">Total vendido</div>
        <div class="st-stat-sub">desde o início</div>
      </div>
    </div>

    {{-- Vender vouchers --}}
    <div class="st-panel">
      <div class="st-panel-title">🏷️ Vender voucher ao cliente</div>

      @if($plans->isEmpty() || $availableByPlan->isEmpty())
        <div class="st-alert error">
          <span>📭</span>
          <div><strong>Sem stock disponível.</strong> Contacte o Agente Revendedor para reabastecer.</div>
        </div>
      @else
        <p style="font-size:.88rem;color:#64748b;margin:0 0 1rem;">Seleccione o plano, insira o nome do cliente (opcional) e clique em <strong>Vender</strong>. O código é gerado automaticamente.</p>
        <div class="st-plan-grid">
          @foreach($plans as $plan)
            @php $stock = $availableByPlan[$plan->slug]->qty ?? 0; @endphp
            <div class="st-plan-card {{ $stock === 0 ? 'out' : '' }}">
              <div class="st-plan-name">{{ $plan->name }}</div>
              <div>
                <div class="st-plan-price">{{ number_format($plan->price_public_aoa, 0, ',', '.') }}<span> Kz</span></div>
                <div style="font-size:.8rem;color:#64748b;">{{ $plan->validity_label }} · {{ $plan->speed_label }}</div>
              </div>
              <div class="st-plan-stock {{ $stock > 0 ? 'ok' : 'out' }}">
                {{ $stock > 0 ? "✔ {$stock} disponíveis" : '✗ Sem stock' }}
              </div>
              @if($stock > 0)
                <form action="{{ route('staff.sell') }}" method="POST">
                  @csrf
                  <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                  <input type="text" name="customer_ref"
                         placeholder="Nome do cliente (opcional)"
                         style="width:100%;box-sizing:border-box;padding:.45rem .7rem;border:1.5px solid #e2e8f0;border-radius:.5rem;font-size:.85rem;margin-bottom:.5rem;background:#f8fafc;"
                         maxlength="100">
                  <button type="submit" class="st-btn-sell">Vender →</button>
                </form>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Histórico --}}
    @if($recentSales->isNotEmpty())
    <div class="st-panel" style="margin-bottom:0;">
      <div class="st-panel-title">🕐 Histórico de vendas</div>
      <div class="st-hist-wrap">
        <table class="st-hist-table">
          <thead>
            <tr>
              <th>Data</th>
              <th>Plano</th>
              <th>Código</th>
              <th>Cliente</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recentSales as $sale)
              <tr>
                <td style="white-space:nowrap;color:#64748b;font-size:.82rem;">
                  {{ optional($sale->reseller_distributed_at)->format('d/m/Y H:i') }}
                </td>
                <td style="font-weight:600;">{{ $sale->plan_id }}</td>
                <td><span class="st-code-mono">{{ $sale->code }}</span></td>
                <td style="color:#64748b;">{{ $sale->reseller_customer_ref ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

  @endif

</div>
</div>
@endsection
