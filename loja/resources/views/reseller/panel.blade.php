@extends('layouts.app')

@push('styles')
<style>
/* ── Painel Revendedor ── */
.rv-page {
  min-height: 80vh;
  background: #f8fafc;
  padding: 2.5rem 1rem 4rem;
}

/* Login */
.rv-login-wrap {
  max-width: 460px;
  margin: 3rem auto 0;
}
.rv-login-card {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 4px 32px rgba(0,0,0,.08);
  padding: 2.5rem 2.25rem 2rem;
}
.rv-login-card .rv-brand {
  display: flex;
  align-items: center;
  gap: .75rem;
  margin-bottom: 1.75rem;
}
.rv-login-card .rv-brand img {
  width: 44px;
  height: 44px;
  border-radius: .5rem;
  object-fit: cover;
}
.rv-login-card .rv-brand-text {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}
.rv-login-card .rv-brand-text span {
  display: block;
  font-size: .78rem;
  font-weight: 500;
  color: #64748b;
}
.rv-login-card h2 {
  font-size: 1.45rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: .35rem;
}
.rv-login-card p.rv-sub {
  font-size: .9rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}
.rv-field { margin-bottom: 1.1rem; }
.rv-field label {
  display: block;
  font-size: .85rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: .35rem;
}
.rv-field input {
  width: 100%;
  padding: .7rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .6rem;
  font-size: .95rem;
  color: #0f172a;
  background: #f8fafc;
  transition: border-color .2s, box-shadow .2s;
  box-sizing: border-box;
}
.rv-field input:focus {
  outline: none;
  border-color: #f7b500;
  box-shadow: 0 0 0 3px rgba(247,181,0,.15);
  background: #fff;
}
.rv-login-note {
  font-size: .82rem;
  color: #64748b;
  margin-bottom: 1.25rem;
  line-height: 1.5;
}
.rv-btn-login {
  display: block;
  width: 100%;
  padding: .8rem 1.5rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: .65rem;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s, transform .15s;
  text-align: center;
}
.rv-btn-login:hover { background: #e0a800; transform: translateY(-1px); }

/* Dashboard */
.rv-dash {
  max-width: 1100px;
  margin: 0 auto;
}
.rv-topbar {
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
.rv-topbar-left { display: flex; align-items: center; gap: .9rem; }
.rv-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg,#0d9488,#0891b2);
  color: #fff;
  font-size: 1.15rem;
  font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.rv-topbar-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
.rv-topbar-email { font-size: .82rem; color: #64748b; margin-top: .1rem; }
.rv-mode-badge {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .25rem .7rem;
  border-radius: 9999px;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: .01em;
}
.rv-mode-badge.own  { background: #dbeafe; color: #1d4ed8; }
.rv-mode-badge.wifi { background: #fef3c7; color: #92400e; }
.rv-logout-btn {
  padding: .5rem 1.1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .6rem;
  background: #fff;
  font-size: .85rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: border-color .2s, color .2s;
}
.rv-logout-btn:hover { border-color: #dc2626; color: #dc2626; }

/* Alerts */
.rv-alert {
  border-radius: .75rem;
  padding: .9rem 1.1rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  font-size: .92rem;
}
.rv-alert.danger  { background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; }
.rv-alert.warning { background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e; }
.rv-alert-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: -.05rem; }
.rv-alert strong { font-weight: 700; display: block; margin-bottom: .15rem; }

/* Stats grid */
.rv-stats {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.rv-stat-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.25rem 1.35rem;
  border-top: 3px solid transparent;
}
.rv-stat-card.green  { border-color: #16a34a; }
.rv-stat-card.blue   { border-color: #2563eb; }
.rv-stat-card.amber  { border-color: #f59e0b; }
.rv-stat-card.purple { border-color: #7c3aed; }
.rv-stat-icon { font-size: 1.6rem; margin-bottom: .55rem; }
.rv-stat-label { font-size: .78rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .25rem; }
.rv-stat-value { font-size: 1.55rem; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: .35rem; }
.rv-stat-value.green  { color: #16a34a; }
.rv-stat-value.blue   { color: #2563eb; }
.rv-stat-sub { font-size: .82rem; color: #64748b; }
.rv-progress-bar {
  background: #e5e7eb;
  border-radius: 9999px;
  height: 8px;
  margin-top: .6rem;
  overflow: hidden;
}
.rv-progress-fill {
  height: 8px;
  border-radius: 9999px;
  transition: width .5s ease;
}

/* Panels */
.rv-panel {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.5rem 1.5rem;
  margin-bottom: 1.25rem;
}
.rv-panel-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0f172a;
  padding-bottom: .75rem;
  margin-bottom: 1rem;
  border-bottom: 1.5px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.rv-panel-title .rv-panel-icon { font-size: 1.1rem; }

/* Discount table */
.rv-disc-table {
  width: 100%;
  max-width: 460px;
  border-collapse: collapse;
  font-size: .92rem;
}
.rv-disc-table th {
  text-align: left;
  padding: .5rem .85rem;
  font-size: .78rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .04em;
  background: #f8fafc;
  border-bottom: 1.5px solid #e2e8f0;
}
.rv-disc-table th:last-child { text-align: right; }
.rv-disc-table td {
  padding: .55rem .85rem;
  border-bottom: 1px solid #f1f5f9;
  color: #374151;
}
.rv-disc-table td:last-child { text-align: right; font-weight: 700; color: #0f172a; }
.rv-disc-table tr.active td {
  background: #f0fdf4;
  color: #166534;
  font-weight: 700;
}
.rv-disc-table tr.active td:last-child { color: #16a34a; }
.rv-disc-table .rv-disc-current-chip {
  display: inline-block;
  background: #dcfce7;
  color: #15803d;
  font-size: .7rem;
  font-weight: 700;
  padding: .1rem .5rem;
  border-radius: 9999px;
  margin-left: .5rem;
  letter-spacing: .03em;
  vertical-align: middle;
}

/* Purchase form */
.rv-purchase-form {
  display: flex;
  gap: .75rem;
  align-items: flex-end;
  flex-wrap: wrap;
}
.rv-purchase-form .rv-field { flex: 1; min-width: 200px; margin-bottom: 0; }
.rv-btn-buy {
  padding: .72rem 1.5rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: .65rem;
  font-size: .97rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s, transform .15s;
  white-space: nowrap;
}
.rv-btn-buy:hover { background: #e0a800; transform: translateY(-1px); }
.rv-field-error { color: #dc2626; font-size: .85rem; margin-top: .4rem; }

/* History table */
.rv-hist-wrap { overflow-x: auto; }
.rv-hist-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .9rem;
  min-width: 580px;
}
.rv-hist-table th {
  text-align: left;
  padding: .55rem .85rem;
  font-size: .75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .04em;
  background: #f8fafc;
  border-bottom: 1.5px solid #e2e8f0;
}
.rv-hist-table th.r { text-align: right; }
.rv-hist-table td {
  padding: .6rem .85rem;
  border-bottom: 1px solid #f8fafc;
  color: #374151;
}
.rv-hist-table td.r { text-align: right; }
.rv-hist-table td.muted { color: #94a3b8; font-size: .82rem; }
.rv-hist-table td.bold { font-weight: 700; color: #0f172a; }
.rv-hist-table td.disc { color: #16a34a; font-weight: 700; }
.rv-hist-table tr:last-child td { border-bottom: none; }
.rv-hist-table tr:hover td { background: #f8fafc; }
.rv-csv-btn {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .3rem .7rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .45rem;
  font-size: .8rem;
  font-weight: 600;
  color: #64748b;
  text-decoration: none;
  transition: border-color .2s, color .2s, background .2s;
}
.rv-csv-btn:hover { border-color: #f7b500; color: #92400e; background: #fffbeb; }
.rv-empty { color: #94a3b8; font-size: .95rem; text-align: center; padding: 2rem 0; }
.rv-pagination { margin-top: .75rem; }

@media (max-width: 640px) {
  .rv-topbar { flex-direction: column; align-items: flex-start; }
  .rv-panel { padding: 1.1rem 1rem; }
  .rv-purchase-form { flex-direction: column; }
  .rv-btn-buy { width: 100%; text-align: center; }
}
</style>
@endpush

@section('content')
<div class="rv-page">

  {{-- ── Flash messages ──────────────────────────────────────── --}}
  @if(session('status'))
    <div style="max-width:{{ $application ? '1100px' : '460px' }};margin:0 auto 1rem;">
      <div class="rv-alert" style="background:#f0fdf4;border:1.5px solid #86efac;color:#15803d;">
        <span class="rv-alert-icon">✅</span>
        <div>{{ session('status') }}</div>
      </div>
    </div>
  @endif
  @if(session('error'))
    <div style="max-width:{{ $application ? '1100px' : '460px' }};margin:0 auto 1rem;">
      <div class="rv-alert danger">
        <span class="rv-alert-icon">⚠️</span>
        <div>{{ session('error') }}</div>
      </div>
    </div>
  @endif

  @if(!$application)
    {{-- ════════════════ LOGIN / OTP ════════════════ --}}
    <div class="rv-login-wrap">
      <div class="rv-login-card">
        <div class="rv-brand">
          <img src="{{ asset('img/logo2.jpeg') }}" alt="AngolaWiFi">
          <div class="rv-brand-text">
            AngolaWiFi
            <span>Portal de Revendedores</span>
          </div>
        </div>

        @if($otpPending)
          {{-- PASSO 2: introduzir código --}}
          <h2>Verificação por email</h2>
          <p class="rv-sub">
            Enviámos um código de <strong>6 dígitos</strong> para <strong>{{ $otpEmail }}</strong>.<br>
            Verifique a caixa de entrada (e o spam). Válido durante <strong>10 minutos</strong>.
          </p>
          <form action="{{ route('reseller.panel.verify') }}" method="POST" novalidate autocomplete="off">
            @csrf
            <div class="rv-field">
              <label for="rev-otp">Código de verificação</label>
              <input id="rev-otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}"
                     maxlength="6" placeholder="_ _ _ _ _ _" required autofocus
                     style="font-size:1.6rem;letter-spacing:.35em;text-align:center;font-family:monospace;" />
            </div>
            <button type="submit" class="rv-btn-login">Confirmar código →</button>
          </form>
          <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin-top:.75rem;">
            @csrf
            <button type="submit" class="rv-logout-btn" style="width:100%;text-align:center;">← Usar outro email</button>
          </form>

        @else
          {{-- PASSO 1: introduzir email --}}
          <h2>Entrar no painel</h2>
          <p class="rv-sub">Acesso exclusivo para revendedores aprovados.</p>
          <form action="{{ route('reseller.panel.login') }}" method="POST" novalidate>
            @csrf
            <div class="rv-field">
              <label for="rev-email">Email de revendedor</label>
              <input id="rev-email" name="email" type="email"
                     placeholder="revendedor@exemplo.ao"
                     value="{{ old('email') }}" required autocomplete="email" />
            </div>
            <p class="rv-login-note">
              Receberá um código de verificação neste endereço.<br>
              Acesso restrito a revendedores com candidatura <strong>aprovada</strong>.
            </p>
            <button type="submit" class="rv-btn-login">Enviar código →</button>
          </form>
        @endif

      </div>
    </div>

  @else
    {{-- ════════════════ DASHBOARD ════════════════ --}}
    <div class="rv-dash">

      {{-- Top bar --}}
      <div class="rv-topbar">
        <div class="rv-topbar-left">
          <div class="rv-avatar">{{ mb_strtoupper(mb_substr($application->full_name, 0, 1)) }}</div>
          <div>
            <div class="rv-topbar-name">{{ $application->full_name }}</div>
            <div class="rv-topbar-email">{{ $application->email }}</div>
          </div>
          @if($application->reseller_mode === 'own')
            <span class="rv-mode-badge own">Modo 1 — Internet Própria</span>
          @elseif($application->reseller_mode === 'angolawifi')
            <span class="rv-mode-badge wifi">Modo 2 — Internet AngolaWiFi</span>
          @endif
        </div>
        <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin:0;">
          @csrf
          <button type="submit" class="rv-logout-btn">Terminar sessão</button>
        </form>
      </div>

      {{-- Alerts --}}
      @if($application->maintenanceDueThisMonth())
        <div class="rv-alert danger">
          <span class="rv-alert-icon">🔔</span>
          <div>
            <strong>Taxa de manutenção em atraso</strong>
            Valor em dívida: {{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz.
            Por favor contacte a AngolaWiFi para regularizar.
          </div>
        </div>
      @endif
      @if($application->monthly_target_aoa > 0 && !$application->metMonthlyTarget())
        @php $remaining = $application->monthly_target_aoa - $application->monthlySpendings(); @endphp
        <div class="rv-alert warning">
          <span class="rv-alert-icon">🎯</span>
          <div>
            <strong>Meta mensal ainda não atingida</strong>
            Faltam <strong>{{ number_format($remaining, 0, ',', '.') }} Kz</strong> para atingir a meta
            de {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz este mês.
          </div>
        </div>
      @endif

      {{-- Stats --}}
      <div class="rv-stats">
        <div class="rv-stat-card green">
          <div class="rv-stat-icon">💰</div>
          <div class="rv-stat-label">Lucro estimado</div>
          <div class="rv-stat-value green">{{ number_format($estimatedProfit, 0, ',', '.') }} Kz</div>
          <div class="rv-stat-sub">Soma dos descontos obtidos</div>
        </div>

        <div class="rv-stat-card blue">
          <div class="rv-stat-icon">📦</div>
          <div class="rv-stat-label">Vouchers adquiridos</div>
          <div class="rv-stat-value">{{ number_format($totals['codes_total'], 0, ',', '.') }}</div>
          <div class="rv-stat-sub">
            Bruto: {{ number_format($totals['total_gross'], 0, ',', '.') }} Kz ·
            Líquido: {{ number_format($totals['total_net'], 0, ',', '.') }} Kz
          </div>
        </div>

        @if($application->monthly_target_aoa > 0)
          @php
            $pct = min(100, round($application->monthlySpendings() * 100 / $application->monthly_target_aoa));
          @endphp
          <div class="rv-stat-card amber">
            <div class="rv-stat-icon">🎯</div>
            <div class="rv-stat-label">Meta mensal</div>
            <div class="rv-stat-value">{{ number_format($application->monthlySpendings(), 0, ',', '.') }} Kz</div>
            <div class="rv-stat-sub">de {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz · {{ $pct }}%</div>
            <div class="rv-progress-bar">
              <div class="rv-progress-fill" style="width:{{ $pct }}%;background:{{ $pct >= 100 ? '#16a34a' : '#f59e0b' }};"></div>
            </div>
          </div>
        @endif

        @if($application->bonus_vouchers_aoa > 0)
          <div class="rv-stat-card purple">
            <div class="rv-stat-icon">🎁</div>
            <div class="rv-stat-label">Bónus de arranque</div>
            <div class="rv-stat-value blue">{{ number_format($application->bonus_vouchers_aoa, 0, ',', '.') }} Kz</div>
            <div class="rv-stat-sub">50% da taxa de instalação em vouchers</div>
          </div>
        @endif
      </div>

      {{-- Tabela de descontos --}}
      <div class="rv-panel">
        <div class="rv-panel-title"><span class="rv-panel-icon">🏷️</span> Tabela de descontos</div>
        @if($application->reseller_mode === 'own')
          <p style="font-size:.95rem;color:#374151;">
            Modo 1 — Internet Própria: desconto fixo de
            <strong style="color:#0d9488;">{{ config('reseller.mode_own_discount_percent', 70) }}%</strong>
            em todas as compras.
          </p>
        @else
          @php
            $tiers    = config('reseller.mode_angolawifi_discount_tiers', []);
            $tierKeys = array_values(array_keys($tiers));
            $mySpend  = $application->monthlySpendings();
          @endphp
          <table class="rv-disc-table">
            <thead>
              <tr>
                <th>Compra mensal (Kz)</th>
                <th style="text-align:right;">Desconto</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tiers as $min => $pct)
                @php
                  $idx     = array_search($min, $tierKeys);
                  $nextMin = isset($tierKeys[$idx + 1]) ? $tierKeys[$idx + 1] : null;
                  $active  = $mySpend >= $min && (!$nextMin || $mySpend < $nextMin);
                @endphp
                <tr class="{{ $active ? 'active' : '' }}">
                  <td>
                    {{ number_format($min, 0, ',', '.') }}{{ $nextMin ? ' – '.number_format($nextMin - 1, 0, ',', '.') : '+' }} Kz
                    @if($active)<span class="rv-disc-current-chip">actual</span>@endif
                  </td>
                  <td>{{ $pct }}%</td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <p style="font-size:.8rem;color:#94a3b8;margin-top:.65rem;">O escalão em destaque corresponde ao seu volume de compras deste mês.</p>
        @endif
      </div>

      {{-- Comprar vouchers --}}
      <div class="rv-panel">
        <div class="rv-panel-title"><span class="rv-panel-icon">🛒</span> Comprar vouchers</div>
        <p style="font-size:.9rem;color:#64748b;margin-bottom:1.1rem;">
          Valor mínimo de compra: <strong>{{ number_format($minPurchase, 0, ',', '.') }} Kz</strong> ·
          1 voucher = {{ number_format(config('reseller.code_unit_price_aoa', 1000), 0, ',', '.') }} Kz ·
          Os códigos são gerados automaticamente após a confirmação.
        </p>
        <form action="{{ route('reseller.panel.purchase') }}" method="POST" class="rv-purchase-form">
          @csrf
          <div class="rv-field">
            <label for="gross_amount_aoa">Valor a investir (Kz)</label>
            <input id="gross_amount_aoa" name="gross_amount_aoa" type="number"
                   min="{{ $minPurchase }}" step="1000"
                   value="{{ old('gross_amount_aoa', $minPurchase) }}" required />
          </div>
          <button type="submit" class="rv-btn-buy">Gerar vouchers</button>
        </form>
        @error('gross_amount_aoa')
          <p class="rv-field-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Histórico --}}
      <div class="rv-panel" style="margin-bottom:0;">
        <div class="rv-panel-title"><span class="rv-panel-icon">📋</span> Histórico de compras</div>
        @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->count())
          <div class="rv-hist-wrap">
            <table class="rv-hist-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Data</th>
                  <th class="r">Bruto (Kz)</th>
                  <th class="r">Desconto</th>
                  <th class="r">Líquido (Kz)</th>
                  <th class="r">Vouchers</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach($purchases as $purchase)
                  <tr>
                    <td class="muted">#{{ $purchase->id }}</td>
                    <td>{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="r">{{ number_format($purchase->gross_amount_aoa, 0, ',', '.') }}</td>
                    <td class="r disc">{{ $purchase->discount_percent }}%</td>
                    <td class="r bold">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }}</td>
                    <td class="r">{{ $purchase->codes_count }}</td>
                    <td>
                      <a href="{{ route('reseller.panel.purchase.csv', ['purchase' => $purchase->id]) }}"
                         class="rv-csv-btn">⬇ CSV</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="rv-pagination">{{ $purchases->links() }}</div>
        @else
          <p class="rv-empty">Ainda não existem compras registadas.</p>
        @endif
      </div>

    </div>
  @endif

</div>
@endsection
