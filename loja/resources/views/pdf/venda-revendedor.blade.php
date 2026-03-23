<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Venda — {{ $totalCodes }} voucher(s) · {{ $saleDate->format('d/m/Y H:i') }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 12px;
      color: #1a202c;
      background: #fff;
    }
    /* Header */
    .header {
      border-bottom: 2px solid #f7b500;
      padding-bottom: 12px;
      margin-bottom: 16px;
      display: table;
      width: 100%;
    }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }
    .brand        { font-size: 18px; font-weight: bold; color: #0f172a; }
    .brand-sub    { font-size: 10px; color: #64748b; }
    .doc-title    { font-size: 15px; font-weight: bold; color: #0f172a; }
    .doc-sub      { font-size: 10px; color: #64748b; margin-top: 2px; }

    /* Info grid */
    .info-grid {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 16px;
      display: table;
      width: 100%;
    }
    .info-cell { display: table-cell; width: 33%; padding-right: 12px; vertical-align: top; }
    .info-label { font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .info-val   { font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px; }
    .info-val.green { color: #16a34a; }

    /* Stats bar */
    .stats-bar { display: table; width: 100%; margin-bottom: 16px; border-collapse: separate; border-spacing: 8px 0; }
    .stat-box {
      display: table-cell;
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 8px 12px;
      text-align: center;
    }
    .stat-box .sv { font-size: 18px; font-weight: bold; }
    .stat-box .sl { font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 2px; }
    .stat-box.green { border-color: #86efac; background: #f0fdf4; }
    .stat-box.green .sv { color: #16a34a; }

    /* Plan section title */
    .plan-section {
      background: #1e293b;
      color: #fff;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 4px;
      margin-top: 14px;
    }
    .plan-section:first-of-type { margin-top: 0; }

    /* Table */
    .voucher-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .voucher-table thead tr { background: #f1f5f9; }
    .voucher-table thead th {
      padding: 6px 10px;
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      text-align: left;
      color: #64748b;
      border-bottom: 1px solid #e2e8f0;
    }
    .voucher-table thead th.center { text-align: center; }
    .voucher-table tbody tr:nth-child(even) { background: #f8fafc; }
    .voucher-table tbody td {
      padding: 5px 10px;
      border-bottom: 1px solid #e9ecef;
      font-size: 11px;
      vertical-align: middle;
    }
    .code-cell {
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
      font-weight: bold;
      letter-spacing: 0.06em;
      color: #0f172a;
    }
    .num-cell { text-align: center; color: #64748b; font-size: 10px; }
    .plan-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 99px;
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .plan-badge-diario  { background: #dbeafe; color: #1d4ed8; }
    .plan-badge-semanal { background: #ede9fe; color: #6d28d9; }
    .plan-badge-mensal  { background: #fef3c7; color: #b45309; }
    .plan-badge-default { background: #f1f5f9; color: #475569; }

    /* Activation instructions */
    .instructions {
      background: #f0fdf4;
      border: 1px solid #86efac;
      border-radius: 6px;
      padding: 10px 14px;
      margin-top: 16px;
      margin-bottom: 12px;
      font-size: 11px;
      color: #166534;
      line-height: 1.6;
    }
    .instructions strong { color: #15803d; }

    /* Footer */
    .footer {
      border-top: 1px solid #e2e8f0;
      padding-top: 10px;
      color: #94a3b8;
      font-size: 9px;
      display: table;
      width: 100%;
      margin-top: 16px;
    }
    .footer-left  { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }

    @page { margin: 18mm 14mm; }
  </style>
</head>
<body>

  {{-- Header --}}
  <div class="header">
    <div class="header-left">
      <div class="brand">🌐 AngolaWiFi</div>
      <div class="brand-sub">angolawifi.ao · Vouchers WiFi</div>
    </div>
    <div class="header-right">
      <div class="doc-title">Comprovativo de Venda</div>
      <div class="doc-sub">{{ $saleDate->format('d/m/Y H:i') }}</div>
    </div>
  </div>

  {{-- Info grid --}}
  <div class="info-grid">
    <div class="info-cell">
      <div class="info-label">Revendedor</div>
      <div class="info-val">{{ $application->full_name }}</div>
      <div style="font-size:10px;color:#64748b;margin-top:1px;">{{ $application->phone ?? $application->email }}</div>
    </div>
    <div class="info-cell">
      <div class="info-label">Cliente</div>
      <div class="info-val">{{ $customerRef ?: 'Não identificado' }}</div>
    </div>
    <div class="info-cell" style="padding-right:0;">
      <div class="info-label">Total de vouchers</div>
      <div class="info-val green">{{ $totalCodes }}</div>
      <div style="font-size:10px;color:#64748b;margin-top:1px;">
        {{ $codesByPlan->count() }} tipo(s) de plano
      </div>
    </div>
  </div>

  {{-- Stats --}}
  <div class="stats-bar">
    <div class="stat-box green">
      <div class="sv">{{ $totalCodes }}</div>
      <div class="sl">Voucher(s) vendidos</div>
    </div>
    @foreach($codesByPlan as $planSlug => $codes)
      @php $plan = $voucherPlans->get($planSlug); @endphp
      <div class="stat-box">
        <div class="sv">{{ $codes->count() }}</div>
        <div class="sl">{{ $plan ? $plan->name : $planSlug }}</div>
      </div>
    @endforeach
  </div>

  {{-- Voucher tables per plan --}}
  @php $globalIndex = 0; @endphp
  @foreach($codesByPlan as $planSlug => $codes)
    @php
      $plan = $voucherPlans->get($planSlug);
      $planName = $plan ? $plan->name : $planSlug;
      $validityLabel = $plan ? $plan->validity_label : '';
      $speedLabel = $plan ? $plan->speed_label : '';
      $badgeClass = match($planSlug) {
        'diario'  => 'plan-badge-diario',
        'semanal' => 'plan-badge-semanal',
        'mensal'  => 'plan-badge-mensal',
        default   => 'plan-badge-default',
      };
    @endphp

    <div class="plan-section">
      {{ $planName }}
      @if($validityLabel) — {{ $validityLabel }} @endif
      @if($speedLabel) · {{ $speedLabel }} @endif
      ({{ $codes->count() }} voucher(s))
    </div>

    <table class="voucher-table">
      <thead>
        <tr>
          <th class="center" style="width:28px;">#</th>
          <th>Código de Voucher</th>
          <th>Plano</th>
          @if($validityLabel)<th>Validade</th>@endif
        </tr>
      </thead>
      <tbody>
        @foreach($codes as $code)
          @php $globalIndex++; @endphp
          <tr>
            <td class="num-cell">{{ $globalIndex }}</td>
            <td class="code-cell">{{ $code->code }}</td>
            <td><span class="plan-badge {{ $badgeClass }}">{{ $planName }}</span></td>
            @if($validityLabel)<td style="font-size:10px;color:#64748b;">{{ $validityLabel }}</td>@endif
          </tr>
        @endforeach
      </tbody>
    </table>
  @endforeach

  {{-- Activation instructions --}}
  <div class="instructions">
    <strong>Como activar o voucher:</strong><br>
    1. Ligue-se à rede WiFi <strong>AngolaWiFi</strong>.<br>
    2. O portal de autenticação será aberto automaticamente no seu navegador.<br>
    3. Introduza o código do voucher no campo indicado.<br>
    4. Clique em <strong>"Conectar"</strong> e desfrute da internet.<br><br>
    <strong>Nota:</strong> Cada código é de uso único. Uma vez utilizado, não pode ser reutilizado.
  </div>

  {{-- Footer --}}
  <div class="footer">
    <div class="footer-left">
      AngolaWiFi · Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }} · Comprovativo de venda ao cliente final
    </div>
    <div class="footer-right">
      {{ $totalCodes }} voucher(s) · Revendedor: {{ $application->full_name }}
    </div>
  </div>

</body>
</html>
