@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Painel do Revendedor" style="padding-top:2rem;padding-bottom:2rem;">
  <div class="container">
    <h2>Painel do Revendedor</h2>
    <p class="lead">Área exclusiva para revendedores aprovados comprarem vouchers em quantidade.</p>

    @if(session('status'))
      <div style="background:#dcfce7;border:1px solid #86efac;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.25rem;font-weight:600;">
        {{ session('status') }}
      </div>
    @endif
    @if(session('error'))
      <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.25rem;font-weight:600;">
        {{ session('error') }}
      </div>
    @endif

    @if(!$application)
      {{-- ── Login ─────────────────────────────────────────────── --}}
      <div style="max-width:440px;">
        <div class="plan-card-modern">
          <h3>Identificação</h3>
          <form action="{{ route('reseller.panel.login') }}" method="POST" novalidate style="margin-top:1rem;">
            @csrf
            <div class="form-row">
              <label for="rev-email">E-mail de revendedor *</label>
              <input id="rev-email" name="email" type="email" class="newsletter-input"
                     placeholder="revendedor@exemplo.ao" required />
            </div>
            <p class="auth-footer-note" style="margin-top:.5rem;">
              Utilize o mesmo e-mail usado no formulário "Quero ser revendedor".<br>
              Só revendedores aprovados podem aceder a esta área.
            </p>
            <div style="margin-top:1rem;">
              <button type="submit" class="btn-primary">Entrar</button>
            </div>
          </form>
        </div>
      </div>

    @else
      {{-- ── Header do revendedor ────────────────────────────────── --}}
      <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
        <div>
          <strong>{{ $application->full_name }}</strong>
          <span style="color:#64748b;font-size:.9rem;margin-left:.5rem;">{{ $application->email }}</span>
          @if($application->reseller_mode === 'own')
            <span class="plan-badge" style="background:#dbeafe;color:#1d4ed8;margin-left:.5rem;">Modo 1 – Internet Própria</span>
          @elseif($application->reseller_mode === 'angolawifi')
            <span class="plan-badge" style="background:#fef3c7;color:#92400e;margin-left:.5rem;">Modo 2 – Internet AngolaWiFi</span>
          @endif
        </div>
        <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin:0;">
          @csrf
          <button type="submit" class="btn-ghost" style="font-size:.85rem;padding:.4rem .9rem;">Terminar sessão</button>
        </form>
      </div>

      {{-- ── Alertas ─────────────────────────────────────────────── --}}
      @if($application->maintenanceDueThisMonth())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
          ⚠️ <strong>Taxa de manutenção em atraso!</strong>
          Valor: {{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz.
          Por favor, contacte a AngolaWiFi para regularizar o pagamento.
        </div>
      @endif

      @if($application->monthly_target_aoa > 0 && !$application->metMonthlyTarget())
        @php $remaining = $application->monthly_target_aoa - $application->monthlySpendings(); @endphp
        <div style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
          ⚠️ <strong>Meta mensal não atingida.</strong>
          Ainda precisa de comprar mais <strong>{{ number_format($remaining, 0, ',', '.') }} Kz</strong> este mês
          (meta: {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz).
        </div>
      @endif

      {{-- ── Cards de resumo ─────────────────────────────────────── --}}
      <div class="info-grid" style="margin-bottom:1.5rem;">

        <div class="info-card">
          <h3>💰 Lucro estimado</h3>
          <p style="font-size:1.4rem;font-weight:800;color:#16a34a;">
            {{ number_format($estimatedProfit, 0, ',', '.') }} Kz
          </p>
          <p style="font-size:.85rem;color:#64748b;">Soma dos descontos obtidos em todas as compras.</p>
        </div>

        <div class="info-card">
          <h3>📦 Vouchers adquiridos</h3>
          <p style="font-size:1.4rem;font-weight:800;">{{ number_format($totals['codes_total'], 0, ',', '.') }}</p>
          <p>Bruto total: <strong>{{ number_format($totals['total_gross'], 0, ',', '.') }} Kz</strong></p>
          <p>Líquido total: <strong>{{ number_format($totals['total_net'], 0, ',', '.') }} Kz</strong></p>
        </div>

        @if($application->monthly_target_aoa > 0)
        <div class="info-card">
          <h3>🎯 Meta mensal</h3>
          @php
            $pct = $application->monthly_target_aoa > 0
                ? min(100, round($application->monthlySpendings() * 100 / $application->monthly_target_aoa))
                : 100;
          @endphp
          <p>Comprado este mês: <strong>{{ number_format($application->monthlySpendings(), 0, ',', '.') }} Kz</strong></p>
          <p>Meta: <strong>{{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz</strong></p>
          <div style="background:#e5e7eb;border-radius:9999px;height:10px;margin-top:.5rem;">
            <div style="background:{{ $pct >= 100 ? '#16a34a' : '#f59e0b' }};width:{{ $pct }}%;height:10px;border-radius:9999px;transition:width .4s;"></div>
          </div>
          <p style="font-size:.85rem;color:#64748b;margin-top:.3rem;">{{ $pct }}% atingido</p>
        </div>
        @endif

        @if($application->bonus_vouchers_aoa > 0)
        <div class="info-card">
          <h3>🎁 Bónus de arranque</h3>
          <p style="font-size:1.1rem;font-weight:700;color:#2563eb;">
            {{ number_format($application->bonus_vouchers_aoa, 0, ',', '.') }} Kz
          </p>
          <p style="font-size:.85rem;color:#64748b;">Crédito de vouchers de arranque (50% da taxa de instalação).</p>
        </div>
        @endif

      </div>

      {{-- ── Tabela de descontos ─────────────────────────────────── --}}
      <div class="plan-card-modern" style="margin-bottom:1.5rem;">
        <h3 style="margin-bottom:.75rem;">Tabela de descontos aplicável</h3>
        @if($application->reseller_mode === 'own')
          <p>
            Modo 1 – Internet Própria: desconto fixo de
            <strong>{{ config('reseller.mode_own_discount_percent', 70) }}%</strong>
            em todas as compras.
          </p>
        @else
          @php
            $tiers     = config('reseller.mode_angolawifi_discount_tiers', []);
            $tierKeys  = array_values(array_keys($tiers));
            $mySpend   = $application->monthlySpendings();
          @endphp
          <table style="border-collapse:collapse;font-size:.92rem;width:100%;max-width:420px;">
            <thead>
              <tr style="background:#f8fafc;">
                <th style="padding:.4rem .75rem;text-align:left;border-bottom:1.5px solid #e2e8f0;">Compra mensal (Kz)</th>
                <th style="padding:.4rem .75rem;text-align:right;border-bottom:1.5px solid #e2e8f0;">Desconto</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tiers as $min => $pct)
                @php
                  $idx     = array_search($min, $tierKeys);
                  $nextMin = isset($tierKeys[$idx + 1]) ? $tierKeys[$idx + 1] : null;
                  $active  = $mySpend >= $min && (!$nextMin || $mySpend < $nextMin);
                @endphp
                <tr style="{{ $active ? 'background:#dbeafe;font-weight:700;' : '' }}">
                  <td style="padding:.35rem .75rem;border-bottom:1px solid #f1f5f9;">
                    {{ number_format($min, 0, ',', '.') }}{{ $nextMin ? ' – '.number_format($nextMin - 1, 0, ',', '.') : '+' }} Kz
                  </td>
                  <td style="padding:.35rem .75rem;text-align:right;border-bottom:1px solid #f1f5f9;">{{ $pct }}%</td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <p style="font-size:.82rem;color:#64748b;margin-top:.5rem;">
            O escalão destacado corresponde ao seu volume de compras deste mês.
          </p>
        @endif
      </div>

      {{-- ── Formulário de compra ─────────────────────────────────── --}}
      <div class="plan-card-modern" style="margin-bottom:1.5rem;">
        <h3 style="margin-bottom:.75rem;">Comprar vouchers</h3>
        <p style="font-size:.9rem;color:#64748b;margin-bottom:1rem;">
          Valor mínimo de compra: <strong>{{ number_format($minPurchase, 0, ',', '.') }} Kz</strong>.
          O número de códigos é calculado automaticamente
          (1 voucher = {{ number_format(config('reseller.code_unit_price_aoa', 1000), 0, ',', '.') }} Kz).
        </p>
        <form action="{{ route('reseller.panel.purchase') }}" method="POST"
              style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
          @csrf
          <div class="form-row" style="flex:1;min-width:180px;margin-bottom:0;">
            <label for="gross_amount_aoa">Valor a investir (Kz)</label>
            <input id="gross_amount_aoa" name="gross_amount_aoa" type="number"
                   min="{{ $minPurchase }}" step="1000"
                   value="{{ old('gross_amount_aoa', $minPurchase) }}"
                   class="newsletter-input" required />
          </div>
          <div style="padding-bottom:.05rem;">
            <button type="submit" class="btn-primary">Gerar vouchers</button>
          </div>
        </form>
        @error('gross_amount_aoa')
          <p style="color:#dc2626;font-size:.88rem;margin-top:.5rem;">{{ $message }}</p>
        @enderror
      </div>

      {{-- ── Histórico de compras ────────────────────────────────── --}}
      <div class="plan-card-modern">
        <h3 style="margin-bottom:.75rem;">Histórico de compras</h3>

        @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->count())
          <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.92rem;">
              <thead>
                <tr style="background:#f8fafc;">
                  <th style="text-align:left;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">#</th>
                  <th style="text-align:left;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Data</th>
                  <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Bruto (Kz)</th>
                  <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Desconto</th>
                  <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Líquido (Kz)</th>
                  <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Vouchers</th>
                  <th style="padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;"></th>
                </tr>
              </thead>
              <tbody>
                @foreach($purchases as $purchase)
                  <tr>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;color:#94a3b8;">#{{ $purchase->id }}</td>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;">{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;text-align:right;">{{ number_format($purchase->gross_amount_aoa, 0, ',', '.') }}</td>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;text-align:right;color:#16a34a;font-weight:600;">{{ $purchase->discount_percent }}%</td>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }}</td>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;text-align:right;">{{ $purchase->codes_count }}</td>
                    <td style="padding:.35rem .6rem;border-bottom:1px solid #f1f5f9;">
                      <a href="{{ route('reseller.panel.purchase.csv', ['purchase' => $purchase->id]) }}"
                         class="btn-ghost" style="font-size:.82rem;padding:.25rem .6rem;">⬇ CSV</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div style="margin-top:.75rem;">{{ $purchases->links() }}</div>
        @else
          <p style="color:#64748b;">Ainda não existem compras registadas.</p>
        @endif
      </div>

    @endif
  </div>
</section>
@endsection
