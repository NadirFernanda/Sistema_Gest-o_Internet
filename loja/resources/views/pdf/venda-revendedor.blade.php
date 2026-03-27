<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Comprovativo de Venda &mdash; {{ $totalCodes }} Voucher(s) &middot; {{ $saleDate->format('d/m/Y') }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 11px;
      color: #333333;
      background: #ffffff;
    }

    /* ── HEADER ── */
    .header {
      background: #f7b500;
      padding: 0;
      margin-bottom: 0;
    }
    .header-inner {
      display: table;
      width: 100%;
    }
    .header-left {
      display: table-cell;
      vertical-align: middle;
      padding: 16px 20px;
      width: 55%;
    }
    .header-right {
      display: table-cell;
      vertical-align: middle;
      text-align: right;
      padding: 16px 20px;
      background: #e6a800;
    }
    .brand-name {
      font-size: 26px;
      font-weight: bold;
      color: #1a1a1a;
      letter-spacing: -0.5px;
      line-height: 1;
    }
    .brand-tagline {
      font-size: 9px;
      color: #4a3a00;
      margin-top: 3px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }
    .doc-type {
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: #4a3a00;
      margin-bottom: 4px;
    }
    .doc-title {
      font-size: 15px;
      font-weight: bold;
      color: #1a1a1a;
      line-height: 1.2;
    }
    .doc-date {
      font-size: 9px;
      color: #4a3a00;
      margin-top: 4px;
    }

    /* White divider bar below header */
    .header-bar {
      height: 5px;
      background: #ffffff;
      margin-bottom: 20px;
    }

    /* ── INFO ROW ── */
    .info-row {
      display: table;
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      border: 1px solid #e8e8e8;
    }
    .info-cell {
      display: table-cell;
      width: 33.33%;
      padding: 12px 16px;
      vertical-align: top;
      border-right: 1px solid #e8e8e8;
    }
    .info-cell:last-child { border-right: none; }
    .info-cell.accent { border-top: 3px solid #f7b500; background: #fffdf0; }
    .info-cell.normal { border-top: 3px solid #e8e8e8; background: #fafafa; }
    .info-lbl {
      font-size: 7.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: #999999;
      margin-bottom: 5px;
    }
    .info-val {
      font-size: 12px;
      font-weight: bold;
      color: #1a1a1a;
      line-height: 1.3;
    }
    .info-sub {
      font-size: 9px;
      color: #888888;
      margin-top: 2px;
    }

    /* ── STATS BAR ── */
    .stats-bar {
      display: table;
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 22px;
      background: #f9f9f9;
      border: 1px solid #e8e8e8;
    }
    .stat-cell {
      display: table-cell;
      text-align: center;
      padding: 12px 8px;
      border-right: 1px solid #e8e8e8;
      vertical-align: middle;
    }
    .stat-cell:last-child { border-right: none; }
    .stat-cell.primary {
      background: #f7b500;
    }
    .stat-num {
      font-size: 28px;
      font-weight: bold;
      color: #1a1a1a;
      line-height: 1;
    }
    .stat-cell.primary .stat-num { color: #1a1a1a; }
    .stat-lbl {
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: #666666;
      margin-top: 4px;
    }
    .stat-cell.primary .stat-lbl { color: #4a3a00; }
    .stat-sublbl {
      font-size: 7.5px;
      color: #aaaaaa;
      margin-top: 1px;
    }
    .stat-cell.primary .stat-sublbl { color: #7a6000; }

    /* ── PLAN SECTION ── */
    .plan-section { margin-bottom: 20px; }

    .plan-band {
      display: table;
      width: 100%;
      background: #f7b500;
      padding: 8px 14px;
      margin-bottom: 0;
    }
    .plan-band-left  { display: table-cell; vertical-align: middle; }
    .plan-band-right { display: table-cell; vertical-align: middle; text-align: right; }
    .plan-band-title {
      font-size: 10px;
      font-weight: bold;
      color: #1a1a1a;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .plan-band-meta {
      font-size: 8.5px;
      color: #4a3a00;
      margin-top: 1px;
    }
    .plan-count-badge {
      font-size: 9px;
      font-weight: bold;
      color: #1a1a1a;
      background: #ffffff;
      padding: 2px 10px;
      border-radius: 99px;
    }

    /* ── VOUCHER TABLE ── */
    .voucher-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #e8e8e8;
      border-top: none;
    }
    .voucher-table thead tr {
      background: #f5f5f5;
      border-bottom: 1px solid #e0e0e0;
    }
    .voucher-table thead th {
      padding: 6px 12px;
      font-size: 7.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.09em;
      color: #888888;
      text-align: left;
    }
    .voucher-table thead th.c { text-align: center; }

    .voucher-table tbody tr { border-bottom: 1px solid #f0f0f0; }
    .voucher-table tbody tr:nth-child(even) { background: #fafafa; }
    .voucher-table tbody tr:nth-child(odd)  { background: #ffffff; }

    .voucher-table tbody td {
      padding: 8px 12px;
      font-size: 11px;
      vertical-align: middle;
    }
    .num-cell { text-align: center; color: #bbbbbb; font-size: 9.5px; width: 26px; }

    /* The voucher code box — golden bordered */
    .code-wrap {
      display: inline-block;
      background: #fffbea;
      border: 1.5px solid #f7b500;
      border-radius: 4px;
      padding: 4px 12px;
    }
    .code-text {
      font-family: 'Courier New', Courier, monospace;
      font-size: 13.5px;
      font-weight: bold;
      letter-spacing: 0.14em;
      color: #333333;
    }

    .validity-cell { font-size: 9px; color: #777777; }

    /* Plan badges */
    .plan-badge {
      display: inline-block;
      padding: 3px 9px;
      border-radius: 99px;
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .badge-diario  { background: #fef9c3; color: #713f12; border: 1px solid #fde68a; }
    .badge-semanal { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .badge-mensal  { background: #fef08a; color: #713f12; border: 1px solid #f7b500; }
    .badge-default { background: #f5f5f5; color: #555555; border: 1px solid #e0e0e0; }

    /* ── INSTRUCTIONS ── */
    .instructions {
      margin-top: 24px;
      border: 1px solid #e8e8e8;
      border-top: 3px solid #f7b500;
    }
    .instructions-head {
      background: #fffdf0;
      padding: 8px 16px;
      font-size: 9px;
      font-weight: bold;
      color: #4a3a00;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      border-bottom: 1px solid #fde68a;
    }
    .instructions-body { padding: 14px 16px; background: #ffffff; }

    .instr-table { display: table; width: 100%; border-spacing: 0 7px; border-collapse: separate; }
    .instr-row   { display: table-row; }
    .instr-num   {
      display: table-cell;
      width: 24px;
      height: 24px;
      background: #f7b500;
      color: #1a1a1a;
      font-size: 11px;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
      border-radius: 50%;
    }
    .instr-text  {
      display: table-cell;
      font-size: 10.5px;
      color: #444444;
      padding-left: 11px;
      vertical-align: middle;
      line-height: 1.55;
    }
    .instr-note {
      margin-top: 12px;
      padding: 8px 13px;
      background: #fffbea;
      border-left: 3px solid #f7b500;
      font-size: 9.5px;
      color: #4a3a00;
    }

    /* ── FOOTER ── */
    .footer {
      margin-top: 24px;
      background: #f7b500;
      padding: 10px 18px;
    }
    .footer-inner { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-brand { font-size: 11px; font-weight: bold; color: #1a1a1a; }
    .footer-text  { font-size: 8px; color: #4a3a00; margin-top: 2px; line-height: 1.5; }

    @page { margin: 12mm 14mm; }
  </style>
</head>
<body>

  {{-- ══ HEADER ══ --}}
  <div class="header">
    <div class="header-inner">
      <div class="header-left">
        <div class="brand-name">AngolaWiFi</div>
        <div class="brand-tagline">angolawifi.ao &nbsp;&middot;&nbsp; Vouchers WiFi</div>
      </div>
      <div class="header-right">
        <div class="doc-type">Documento Oficial</div>
        <div class="doc-title">Comprovativo de Venda</div>
        <div class="doc-date">Emitido em {{ $saleDate->format('d/m/Y') }} &#224;s {{ $saleDate->format('H:i') }}</div>
      </div>
    </div>
  </div>
  <div class="header-bar"></div>

  {{-- ══ INFO CARDS ══ --}}
  <div class="info-row">
    <div class="info-cell accent">
      <div class="info-lbl">Revendedor</div>
      <div class="info-val">{{ $application->full_name }}</div>
      <div class="info-sub">{{ $application->phone ?? $application->email }}</div>
    </div>
    <div class="info-cell normal">
      <div class="info-lbl">Cliente</div>
      <div class="info-val">{{ $customerRef ?: 'N&#227;o identificado' }}</div>
      <div class="info-sub">Cliente final</div>
    </div>
    <div class="info-cell normal">
      <div class="info-lbl">Resumo</div>
      <div class="info-val" style="color:#16a34a;">{{ $totalCodes }} voucher(s)</div>
      <div class="info-sub">{{ $codesByPlan->count() }} tipo(s) de plano</div>
    </div>
  </div>

  {{-- ══ STATS BAR ══ --}}
  <div class="stats-bar">
    <div class="stat-cell primary">
      <div class="stat-num">{{ $totalCodes }}</div>
      <div class="stat-lbl">Total vendidos</div>
    </div>
    @foreach($codesByPlan as $planSlug => $codes)
      @php $plan = $voucherPlans->get($planSlug); @endphp
      <div class="stat-cell">
        <div class="stat-num">{{ $codes->count() }}</div>
        <div class="stat-lbl">{{ $plan ? $plan->name : $planSlug }}</div>
        @if($plan)<div class="stat-sublbl">{{ $plan->validity_label }}</div>@endif
      </div>
    @endforeach
  </div>

  {{-- ══ VOUCHER TABLES PER PLAN ══ --}}
  @php $globalIndex = 0; @endphp
  @foreach($codesByPlan as $planSlug => $codes)
    @php
      $plan          = $voucherPlans->get($planSlug);
      $planName      = $plan ? $plan->name : $planSlug;
      $validityLabel = $plan ? $plan->validity_label : '';
      $speedLabel    = $plan ? $plan->speed_label : '';
      $badgeClass    = match($planSlug) {
        'diario'  => 'badge-diario',
        'semanal' => 'badge-semanal',
        'mensal'  => 'badge-mensal',
        default   => 'badge-default',
      };
      $priceLabel = $plan ? number_format($plan->price_public_aoa, 0, ',', '.') . ' Kz' : '';
    @endphp

    <div class="plan-section">
      <div class="plan-band">
        <div class="plan-band-left">
          <div class="plan-band-title">{{ $planName }}</div>
          <div class="plan-band-meta">
            {{ $validityLabel }}{{ ($speedLabel && $validityLabel) ? ' &middot; ' . $speedLabel : $speedLabel }}
            {{ $priceLabel ? ' &middot; ' . $priceLabel . ' / c&#243;digo' : '' }}
          </div>
        </div>
        <div class="plan-band-right">
          <span class="plan-count-badge">{{ $codes->count() }} voucher(s)</span>
        </div>
      </div>

      <table class="voucher-table">
        <thead>
          <tr>
            <th class="c">#</th>
            <th>C&#243;digo de Voucher</th>
            <th>Plano</th>
            @if($validityLabel)<th>Validade</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach($codes as $code)
            @php $globalIndex++; @endphp
            <tr>
              <td class="num-cell">{{ $globalIndex }}</td>
              <td>
                <div class="code-wrap">
                  <span class="code-text">{{ $code->code }}</span>
                </div>
              </td>
              <td><span class="plan-badge {{ $badgeClass }}">{{ $planName }}</span></td>
              @if($validityLabel)<td class="validity-cell">{{ $validityLabel }}</td>@endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endforeach

  {{-- ══ ACTIVATION INSTRUCTIONS ══ --}}
  <div class="instructions">
    <div class="instructions-head">Como activar o voucher</div>
    <div class="instructions-body">
      <div class="instr-table">
        <div class="instr-row">
          <div class="instr-num">1</div>
          <div class="instr-text">Ligue-se &#224; rede WiFi <strong>AngolaWiFi</strong> no seu dispositivo.</div>
        </div>
        <div class="instr-row">
          <div class="instr-num">2</div>
          <div class="instr-text">O portal de autentica&#231;&#227;o ser&#225; aberto automaticamente no navegador.</div>
        </div>
        <div class="instr-row">
          <div class="instr-num">3</div>
          <div class="instr-text">Introduza o c&#243;digo do voucher no campo indicado.</div>
        </div>
        <div class="instr-row">
          <div class="instr-num">4</div>
          <div class="instr-text">Clique em <strong>&#8220;Conectar&#8221;</strong> e desfrute da internet.</div>
        </div>
      </div>
      <div class="instr-note">
        <strong>Aten&#231;&#227;o:</strong> Cada c&#243;digo &#233; de uso &#250;nico e intransmiss&#237;vel. Uma vez activado, n&#227;o pode ser reutilizado.
      </div>
    </div>
  </div>

  {{-- ══ FOOTER ══ --}}
  <div class="footer">
    <div class="footer-inner">
      <div class="footer-left">
        <div class="footer-brand">AngolaWiFi</div>
        <div class="footer-text">
          Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }}<br>
          Comprovativo de venda ao cliente final &mdash; v&#225;lido sem assinatura
        </div>
      </div>
      <div class="footer-right">
        <div class="footer-text" style="text-align:right;">
          {{ $totalCodes }} voucher(s) vendidos<br>
          Revendedor: <strong>{{ $application->full_name }}</strong>
        </div>
      </div>
    </div>
  </div>

</body>
</html>

