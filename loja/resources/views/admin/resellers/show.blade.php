@extends('layouts.app')

@section('title', 'Revendedor — Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1100px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:1rem 1.1rem;}
.ap-stat-val{font-size:1.5rem;font-weight:800;line-height:1;margin:0 0 .15rem;}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-stat-sub{font-size:.78rem;color:var(--a-faint);margin-top:.25rem;}
.ap-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.4rem;margin-bottom:1.25rem;}
.ap-card-title{font-size:.92rem;font-weight:700;margin:0 0 1rem;padding-bottom:.6rem;border-bottom:1px solid var(--a-border);}
.ap-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;}
@media(max-width:640px){.ap-grid-2{grid-template-columns:1fr}}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-hint{font-size:.77rem;color:var(--a-faint);margin-top:.2rem;}
.ap-err-inline{font-size:.8rem;color:var(--a-red);margin-top:.2rem;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-sm{padding:.35rem .8rem;font-size:.8rem;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}
.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}.bg-blue{background:#dbeafe;color:#1d4ed8;}
.ap-dl{display:grid;grid-template-columns:1fr 1fr;gap:.5rem 1.5rem;font-size:.88rem;}
@media(max-width:600px){.ap-dl{grid-template-columns:1fr}}
.ap-dl dt{color:var(--a-muted);font-size:.8rem;margin-bottom:.1rem;}
.ap-dl dd{font-weight:600;margin:0;}
.ap-bar{background:#e5e7eb;border-radius:9999px;height:8px;overflow:hidden;margin-top:.35rem;}
.ap-bar-fill{height:8px;border-radius:9999px;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.6rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table th.r,.ap-table td.r{text-align:right;}
.ap-table td{padding:.55rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-tcard-head{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1rem;border-bottom:1px solid var(--a-border);}
.ap-tcard-head-title{font-size:.9rem;font-weight:700;margin:0;}
.ap-note{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);color:#78350f;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;line-height:1.55;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>{{ $application->full_name }}</h1>
      <p class="ap-sub">#{{ $application->id }} &middot; {{ $application->email }} &middot; {{ $application->phone }}</p>
    </div>
    <a href="{{ route('admin.resellers.index') }}" class="ap-back">&larr; Candidaturas</a>
  </div>

  @if(session('status'))
    <div class="ap-ok">{{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="ap-err">{{ session('error') }}</div>
  @endif

  <div class="ap-note">
    <strong>O que posso fazer nesta página?</strong> Esta é a ficha completa de um candidato a revendedor. Todas as acções de gestão da candidatura são feitas aqui.<br><br>
    <strong>Secção "Estado da candidatura":</strong><br>
    &bull; Seleccione o estado no menu pendente e clique <strong>Guardar estado</strong> para registar.<br>
    &bull; Ou use os botões rápidos: <strong>Aprovar</strong> (o revendedor fica activo imediatamente e passa a poder fazer compras em bloco de códigos Wi-Fi) ou <strong>Rejeitar</strong> (candidatura recusada, sem acesso ao programa).<br><br>
    <strong>Campo "Notas internas":</strong> Use para anotações privadas do admin (ex: "aguarda documentos", "contactado a 15/03", "não atendeu telefone"). As notas <em>nunca são visíveis para o candidato</em>.<br>
    <strong>Historial de compras:</strong> Na parte inferior da página estão listadas todas as compras em bloco efectuadas por este revendedor, com valores brutos, descontos e datas.
  </div>

  {{-- Estado --}}
  <div class="ap-card">
    <p class="ap-card-title">Estado da candidatura</p>
    <div style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
      <form action="{{ route('admin.resellers.status', $application) }}" method="POST"
            style="display:contents;">
        @csrf @method('PATCH')
        <div style="display:flex;flex-direction:column;gap:.25rem;min-width:180px;">
          <label class="ap-label" for="status">Estado</label>
          <select id="status" name="status" class="ap-ctrl" style="width:auto;">
            <option value="pending"  @selected($application->status === 'pending') >Pendente</option>
            <option value="approved" @selected($application->status === 'approved')>Aprovado</option>
            <option value="rejected" @selected($application->status === 'rejected')>Rejeitado</option>
          </select>
        </div>
        <button type="submit" class="ap-btn ap-btn-primary">Guardar estado</button>
        @if($application->status === 'pending')
          <button type="submit" name="status" value="approved" class="ap-btn ap-btn-primary">Aprovar</button>
          <button type="submit" name="status" value="rejected" class="ap-btn ap-btn-primary">Rejeitar</button>
        @endif
      </form>
      <a href="{{ route('admin.resellers.index') }}" class="ap-btn ap-btn-primary">&#8592; Voltar</a>
    </div>
  </div>

  {{-- Resumo financeiro --}}
  <div class="ap-stats">
    <div class="ap-stat">
      <p class="ap-stat-val">{{ number_format($totalRevenue, 0, ',', '.') }}</p>
      <p class="ap-stat-lbl">Receita l&iacute;quida (Kz)</p>
      <p class="ap-stat-sub">Total pago &agrave; AngolaWiFi</p>
    </div>
    <div class="ap-stat">
      <p class="ap-stat-val" style="color:var(--a-green);">{{ number_format($totalProfit, 0, ',', '.') }}</p>
      <p class="ap-stat-lbl">Lucro revendedor (Kz)</p>
      <p class="ap-stat-sub">Soma de todos os descontos</p>
    </div>
    <div class="ap-stat">
      <p class="ap-stat-lbl" style="margin-bottom:.3rem;">Meta este m&ecirc;s</p>
      @if($application->monthly_target_aoa > 0)
        @php $pct = min(100, round($monthlySpend * 100 / $application->monthly_target_aoa)); @endphp
        <p class="ap-stat-val" style="font-size:1rem;font-weight:700;">
          {{ number_format($monthlySpend, 0, ',', '.') }} / {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz
        </p>
        <div class="ap-bar"><div class="ap-bar-fill" style="width:{{ $pct }}%;background:{{ $pct >= 100 ? 'var(--a-green)' : 'var(--a-brand)' }};"></div></div>
        <p class="ap-stat-sub">{{ $pct }}% atingido</p>
      @else
        <p class="ap-stat-sub">Sem meta definida</p>
      @endif
    </div>
    <div class="ap-stat">
      <p class="ap-stat-lbl" style="margin-bottom:.3rem;">Manuten&ccedil;&atilde;o</p>
      @if($application->maintenanceDueThisMonth())
        <span class="badge bg-red">Pendente este m&ecirc;s</span>
        <p class="ap-stat-sub">{{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz</p>
      @elseif($application->maintenance_paid_year >= now()->year)
        <span class="badge bg-green">Paga ({{ $application->maintenance_paid_year }})</span>
      @else
        <p class="ap-stat-sub">Sem dados</p>
      @endif
    </div>
  </div>

  {{-- Configuração --}}
  <div class="ap-card">
    <p class="ap-card-title">Configura&ccedil;&atilde;o do revendedor</p>
    <form action="{{ route('admin.resellers.update', $application) }}" method="POST">
      @csrf @method('PUT')
      @php
        $modeDefault = old('reseller_mode', $application->reseller_mode ?? $application->internet_type);
      @endphp

      <div class="ap-grid-2" style="margin-bottom:1rem;">

        <div>
          <label class="ap-label" for="reseller_mode">Modo de revenda</label>
          <select id="reseller_mode" name="reseller_mode" class="ap-ctrl">
            <option value="">-- N&atilde;o definido --</option>
            <option value="own"        @selected($modeDefault === 'own')       >Modo 1 &ndash; Internet Pr&oacute;pria (70% desconto fixo)</option>
            <option value="angolawifi" @selected($modeDefault === 'angolawifi')>Modo 2 &ndash; Internet AngolaWiFi (escalonado)</option>
          </select>
          <p class="ap-hint">Candidato solicitou:
            @if($application->internet_type === 'own') <strong>Internet pr&oacute;pria no local</strong>
            @elseif($application->internet_type === 'angolawifi') <strong>Internet pela AngolaWiFi</strong>
            @else <em>n&atilde;o especificado</em>
            @endif
          </p>
          @error('reseller_mode')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ap-label" for="installation_fee_aoa">Taxa de instala&ccedil;&atilde;o (Kz)</label>
          <input id="installation_fee_aoa" name="installation_fee_aoa" type="number" min="0" step="1000"
                 value="{{ old('installation_fee_aoa', $application->installation_fee_aoa) }}"
                 class="ap-ctrl" placeholder="ex: 100000">
          <p class="ap-hint">B&oacute;nus de arranque = 50% da taxa; meta mensal (Modo 1) = 50% da taxa.</p>
          @error('installation_fee_aoa')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ap-label" for="monthly_target_aoa">Meta mensal (Kz)</label>
          <input id="monthly_target_aoa" name="monthly_target_aoa" type="number" min="0" step="1000"
                 value="{{ old('monthly_target_aoa', $application->monthly_target_aoa) }}"
                 class="ap-ctrl" placeholder="ex: 50000">
          <p class="ap-hint">Calculada automaticamente se a taxa de instala&ccedil;&atilde;o for definida (Modo 1).</p>
          @error('monthly_target_aoa')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ap-label" for="maintenance_paid_year">Manuten&ccedil;&atilde;o paga &mdash; ano</label>
          <input id="maintenance_paid_year" name="maintenance_paid_year" type="number"
                 min="2020" max="2100"
                 value="{{ old('maintenance_paid_year', $application->maintenance_paid_year) }}"
                 class="ap-ctrl" placeholder="{{ now()->year }}">
          @error('maintenance_paid_year')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="ap-label" for="maintenance_status">Estado da manuten&ccedil;&atilde;o</label>
          <select id="maintenance_status" name="maintenance_status" class="ap-ctrl">
            <option value="">-- N&atilde;o definido --</option>
            <option value="ok"      @selected($application->maintenance_status === 'ok')     >Regularizado</option>
            <option value="pending" @selected($application->maintenance_status === 'pending') >Pendente</option>
            <option value="overdue" @selected($application->maintenance_status === 'overdue') >Em atraso</option>
          </select>
          @error('maintenance_status')<p class="ap-err-inline">{{ $message }}</p>@enderror
        </div>

      </div>

      <div style="margin-bottom:1rem;">
        <label class="ap-label" for="notes">Notas internas</label>
        <textarea id="notes" name="notes" rows="3" class="ap-ctrl"
                  style="resize:vertical;"
                  placeholder="Observa&ccedil;&otilde;es sobre este revendedor...">{{ old('notes', $application->notes) }}</textarea>
        @error('notes')<p class="ap-err-inline">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="ap-btn ap-btn-primary">Guardar altera&ccedil;&otilde;es</button>
    </form>
  </div>

  {{-- Dados da candidatura --}}
  <div class="ap-card">
    <p class="ap-card-title">Dados da candidatura</p>
    <dl class="ap-dl">
      <div><dt>BI / NIF</dt><dd>{{ $application->document_number }}</dd></div>
      <div><dt>Endere&ccedil;o</dt><dd>{{ $application->address }}</dd></div>
      <div><dt>Local de instala&ccedil;&atilde;o</dt><dd>{{ $application->installation_location }}</dd></div>
      <div>
        <dt>Internet solicitada</dt>
        <dd>
          @if($application->internet_type === 'own') Internet pr&oacute;pria no local
          @elseif($application->internet_type === 'angolawifi') Internet pela AngolaWiFi
          @else <span style="color:var(--a-faint);">&mdash;</span>
          @endif
        </dd>
      </div>
      <div><dt>Candidatura enviada</dt><dd>{{ optional($application->created_at)->format('d/m/Y H:i') }}</dd></div>
      <div><dt>B&oacute;nus de arranque</dt><dd>{{ number_format($application->bonus_vouchers_aoa ?? 0, 0, ',', '.') }} Kz</dd></div>
    </dl>
    @if($application->message)
      <div style="margin-top:.85rem;padding-top:.85rem;border-top:1px solid var(--a-border);">
        <p class="ap-hint" style="margin-bottom:.35rem;">Mensagem do candidato:</p>
        <blockquote style="margin:.25rem 0 0 .75rem;padding-left:.75rem;border-left:3px solid var(--a-border);color:#374151;font-size:.88rem;">
          {{ $application->message }}
        </blockquote>
      </div>
    @endif
  </div>

  {{-- Hist&oacute;rico de compras --}}
  <div class="ap-tcard">
    <div class="ap-tcard-head">
      <p class="ap-tcard-head-title">Hist&oacute;rico de compras</p>
      <a href="{{ route('admin.resellers.purchases.index', ['reseller_id' => $application->id]) }}"
         class="ap-btn ap-btn-primary ap-btn-sm">Ver todas &rarr;</a>
    </div>
    @if($purchases->count())
      <table class="ap-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Data</th>
            <th class="r">Bruto (Kz)</th>
            <th class="r">Desconto</th>
            <th class="r">L&iacute;quido (Kz)</th>
            <th class="r">Vouchers</th>
          </tr>
        </thead>
        <tbody>
          @foreach($purchases as $purchase)
            <tr>
              <td class="dim">#{{ $purchase->id }}</td>
              <td>{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
              <td class="r">{{ number_format($purchase->gross_amount_aoa, 0, ',', '.') }}</td>
              <td class="r" style="color:var(--a-green);font-weight:600;">{{ $purchase->discount_percent }}%</td>
              <td class="r" style="font-weight:700;">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }}</td>
              <td class="r">{{ $purchase->codes_count }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="ap-pager">{{ $purchases->links() }}</div>
    @else
      <div style="padding:2rem 1rem;text-align:center;color:var(--a-faint);">
        <p style="margin:0;font-size:.9rem;">Este revendedor ainda n&atilde;o efectuou compras.</p>
      </div>
    @endif
  </div>

</div></div>
@endsection
