@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Detalhe do revendedor">
  <div class="container">

    {{-- ── Cabeçalho ──────────────────────────────────────────── --}}
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
      <div>
        <h2 style="margin:0;">{{ $application->full_name }}</h2>
        <p class="lead" style="margin:.25rem 0 0;">
          #{{ $application->id }} · {{ $application->email }} · {{ $application->phone }}
        </p>
      </div>
      <a href="{{ route('admin.resellers.index') }}" class="btn-modern" style="font-size:.85rem;padding:.4rem .9rem;">← Candidaturas</a>
    </div>

    @if(session('status'))
      <div style="background:#dcfce7;border:1px solid #86efac;color:#15803d;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1.25rem;font-weight:600;">
        {{ session('status') }}
      </div>
    @endif

    {{-- ── Estado da candidatura ───────────────────────────────── --}}
    <div class="plan-card-modern" style="margin-bottom:1.5rem;">
      <h3 style="margin-bottom:.75rem;">Estado da candidatura</h3>
      <form action="{{ route('admin.resellers.status', $application) }}" method="POST"
            style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
        @csrf @method('PATCH')
        <div class="form-row" style="min-width:160px;margin-bottom:0;">
          <label for="status">Estado</label>
          <select id="status" name="status" class="newsletter-input">
            <option value="pending"  @selected($application->status === 'pending') >Pendente</option>
            <option value="approved" @selected($application->status === 'approved')>Aprovado</option>
            <option value="rejected" @selected($application->status === 'rejected')>Rejeitado</option>
          </select>
        </div>
        <div>
          <button type="submit" class="btn-primary">Guardar estado</button>
        </div>
      </form>
    </div>

    {{-- ── Resumo financeiro ───────────────────────────────────── --}}
    <div class="info-grid" style="margin-bottom:1.5rem;">
      <div class="info-card">
        <h3>💰 Receita (loja)</h3>
        <p style="font-size:1.3rem;font-weight:800;">{{ number_format($totalRevenue, 0, ',', '.') }} Kz</p>
        <p style="font-size:.85rem;color:#64748b;">Valor líquido total pago à AngolaWiFi.</p>
      </div>
      <div class="info-card">
        <h3>📈 Lucro revendedor</h3>
        <p style="font-size:1.3rem;font-weight:800;color:#16a34a;">{{ number_format($totalProfit, 0, ',', '.') }} Kz</p>
        <p style="font-size:.85rem;color:#64748b;">Soma de todos os descontos obtidos.</p>
      </div>
      <div class="info-card">
        <h3>🎯 Meta este mês</h3>
        @if($application->monthly_target_aoa > 0)
          @php $pct = min(100, round($monthlySpend * 100 / $application->monthly_target_aoa)); @endphp
          <p>{{ number_format($monthlySpend, 0, ',', '.') }} / {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz</p>
          <div style="background:#e5e7eb;border-radius:9999px;height:10px;margin-top:.4rem;">
            <div style="background:{{ $pct >= 100 ? '#16a34a' : '#f59e0b' }};width:{{ $pct }}%;height:10px;border-radius:9999px;"></div>
          </div>
          <p style="font-size:.83rem;color:#64748b;margin-top:.25rem;">{{ $pct }}% atingido</p>
        @else
          <p style="color:#64748b;">Sem meta definida.</p>
        @endif
      </div>
      <div class="info-card">
        <h3>🔧 Manutenção</h3>
        @if($application->maintenanceDueThisMonth())
          <p style="color:#dc2626;font-weight:700;">⚠️ Pendente este mês</p>
          <p>{{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz</p>
        @elseif($application->maintenance_paid_year >= now()->year)
          <p style="color:#16a34a;font-weight:700;">✅ Paga ({{ $application->maintenance_paid_year }})</p>
        @else
          <p style="color:#64748b;">Sem dados de manutenção.</p>
        @endif
      </div>
    </div>

    {{-- ── Configuração de negócio ──────────────────────────────── --}}
    <div class="plan-card-modern" style="margin-bottom:1.5rem;">
      <h3 style="margin-bottom:.75rem;">Configuração do revendedor</h3>
      <form action="{{ route('admin.resellers.update', $application) }}" method="POST">
        @csrf @method('PUT')

        <div class="info-grid" style="margin-bottom:1rem;">

          <div class="form-row">
            <label for="reseller_mode">Modo de revenda</label>
            <select id="reseller_mode" name="reseller_mode" class="newsletter-input">
              <option value="">-- Não definido --</option>
              <option value="own"         @selected($application->reseller_mode === 'own')        >Modo 1 – Internet Própria (70% desconto fixo)</option>
              <option value="angolawifi"  @selected($application->reseller_mode === 'angolawifi') >Modo 2 – Internet AngolaWiFi (escalonado)</option>
            </select>
            @error('reseller_mode')<p style="color:#dc2626;font-size:.85rem;">{{ $message }}</p>@enderror
          </div>

          <div class="form-row">
            <label for="installation_fee_aoa">Taxa de instalação (Kz)</label>
            <input id="installation_fee_aoa" name="installation_fee_aoa" type="number" min="0" step="1000"
                   value="{{ old('installation_fee_aoa', $application->installation_fee_aoa) }}"
                   class="newsletter-input" placeholder="ex: 100000" />
            <p style="font-size:.8rem;color:#64748b;margin-top:.25rem;">
              Ao guardar: bónus de arranque = 50% da taxa; meta mensal (Modo 1) = 50% da taxa.
            </p>
            @error('installation_fee_aoa')<p style="color:#dc2626;font-size:.85rem;">{{ $message }}</p>@enderror
          </div>

          <div class="form-row">
            <label for="monthly_target_aoa">Meta mensal (Kz)</label>
            <input id="monthly_target_aoa" name="monthly_target_aoa" type="number" min="0" step="1000"
                   value="{{ old('monthly_target_aoa', $application->monthly_target_aoa) }}"
                   class="newsletter-input" placeholder="ex: 50000" />
            <p style="font-size:.8rem;color:#64748b;margin-top:.25rem;">Calculada automaticamente se a taxa de instalação for definida (Modo 1).</p>
            @error('monthly_target_aoa')<p style="color:#dc2626;font-size:.85rem;">{{ $message }}</p>@enderror
          </div>

          <div class="form-row">
            <label for="maintenance_paid_year">Manutenção paga — ano</label>
            <input id="maintenance_paid_year" name="maintenance_paid_year" type="number"
                   min="2020" max="2100"
                   value="{{ old('maintenance_paid_year', $application->maintenance_paid_year) }}"
                   class="newsletter-input" placeholder="{{ now()->year }}" />
            @error('maintenance_paid_year')<p style="color:#dc2626;font-size:.85rem;">{{ $message }}</p>@enderror
          </div>

          <div class="form-row">
            <label for="maintenance_status">Estado da manutenção</label>
            <select id="maintenance_status" name="maintenance_status" class="newsletter-input">
              <option value="">-- Não definido --</option>
              <option value="ok"      @selected($application->maintenance_status === 'ok')     >&#x2705; Regularizado</option>
              <option value="pending" @selected($application->maintenance_status === 'pending') >&#x23F3; Pendente</option>
              <option value="overdue" @selected($application->maintenance_status === 'overdue') >&#x26A0;&#xFE0F; Em atraso</option>
            </select>
            @error('maintenance_status')<p style="color:#dc2626;font-size:.85rem;">{{ $message }}</p>@enderror
          </div>

        </div>

        <div class="form-row" style="margin-bottom:1rem;">
          <label for="notes">Notas internas</label>
          <textarea id="notes" name="notes" rows="3" class="newsletter-input"
                    style="resize:vertical;"
                    placeholder="Observações sobre este revendedor...">{{ old('notes', $application->notes) }}</textarea>
          @error('notes')<p style="color:#dc2626;font-size:.85rem;">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-primary">Guardar alterações</button>
      </form>
    </div>

    {{-- ── Dados da candidatura ────────────────────────────────── --}}
    <div class="plan-card-modern" style="margin-bottom:1.5rem;">
      <h3 style="margin-bottom:.75rem;">Dados da candidatura</h3>
      <dl style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem;font-size:.9rem;">
        <div><dt style="color:#64748b;">BI / NIF</dt><dd>{{ $application->document_number }}</dd></div>
        <div><dt style="color:#64748b;">Endereço</dt><dd>{{ $application->address }}</dd></div>
        <div><dt style="color:#64748b;">Local de instalação</dt><dd>{{ $application->installation_location }}</dd></div>
        <div><dt style="color:#64748b;">Tipo de internet</dt><dd>{{ $application->internet_type }}</dd></div>
        <div><dt style="color:#64748b;">Candidatura enviada</dt><dd>{{ optional($application->created_at)->format('d/m/Y H:i') }}</dd></div>
        <div><dt style="color:#64748b;">Bónus de arranque</dt><dd>{{ number_format($application->bonus_vouchers_aoa ?? 0, 0, ',', '.') }} Kz</dd></div>
      </dl>
      @if($application->message)
        <div style="margin-top:.75rem;">
          <p style="color:#64748b;font-size:.85rem;">Mensagem do candidato:</p>
          <blockquote style="margin:.25rem 0 0 1rem;padding-left:.75rem;border-left:3px solid #e2e8f0;color:#374151;">
            {{ $application->message }}
          </blockquote>
        </div>
      @endif
    </div>

    {{-- ── Histórico de compras ────────────────────────────────── --}}
    <div class="plan-card-modern">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
        <h3 style="margin:0;">Histórico de compras</h3>
        <a href="{{ route('admin.resellers.purchases.index', ['reseller_id' => $application->id]) }}"
           class="btn-modern" style="font-size:.83rem;padding:.3rem .7rem;">Ver todas →</a>
      </div>

      @if($purchases->count())
        <div style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
              <tr style="background:#f8fafc;">
                <th style="text-align:left;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">#</th>
                <th style="text-align:left;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Data</th>
                <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Bruto (Kz)</th>
                <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Desconto</th>
                <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Líquido (Kz)</th>
                <th style="text-align:right;padding:.4rem .6rem;border-bottom:1.5px solid #e2e8f0;">Vouchers</th>
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
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div style="margin-top:.75rem;">{{ $purchases->links() }}</div>
      @else
        <p style="color:#64748b;">Este revendedor ainda não efectuou compras.</p>
      @endif
    </div>

  </div>
</section>
@endsection
