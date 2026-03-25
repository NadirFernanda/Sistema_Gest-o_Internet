<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Comprovativo de Venda — {{ $totalCodes }} Voucher(s) · {{ $saleDate->format('d/m/Y') }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 11.5px;
      color: #1e293b;
      background: #ffffff;
    }

    /* ── TOP HEADER BAND ── */
    .header-band {
      background: #0f172a;
      padding: 18px 22px 14px;
      margin-bottom: 0;
    }
    .header-inner { display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; }

    .brand-name {
      font-size: 22px;
      font-weight: bold;
      color: #ffffff;
      letter-spacing: -0.3px;
    }
    .brand-accent { color: #f7b500; }
    .brand-tagline {
      font-size: 9px;
      color: #94a3b8;
      margin-top: 2px;
      letter-spacing: 0.08em;
      text-transform: uppercase;
    }
    .doc-label {
      font-size: 9px;
      font-weight: bold;
      color: #f7b500;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      margin-bottom: 3px;
    }
    .doc-title {
      font-size: 17px;
      font-weight: bold;
      color: #ffffff;
    }
    .doc-date {
      font-size: 9.5px;
      color: #94a3b8;
      margin-top: 3px;
    }

    /* Gold accent bar */
    .accent-bar {
      height: 4px;
      background: #f7b500;
      margin-bottom: 18px;
    }

    /* ── INFO CARDS ROW ── */
    .info-row { display: table; width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 18px; }
    .info-card {
      display: table-cell;
      width: 33.33%;
      padding: 12px 16px;
      vertical-align: top;
      border-top: 3px solid #e2e8f0;
      background: #f8fafc;
    }
    .info-card:first-child { border-top-color: #f7b500; background: #fffdf0; }
    .info-card + .info-card { border-left: 1px solid #e2e8f0; }
    .info-card-label {
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #94a3b8;
      margin-bottom: 5px;
    }
    .info-card-value {
      font-size: 13px;
      font-weight: bold;
      color: #0f172a;
      line-height: 1.3;
    }
    .info-card-sub {
      font-size: 9.5px;
      color: #64748b;
      margin-top: 3px;
    }

    /* ── STATS SUMMARY BAR ── */
    .stats-row { display: table; width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .stat-cell {
      display: table-cell;
      padding: 14px 10px;
      text-align: center;
      vertical-align: middle;
      border-right: 1px solid #e2e8f0;
    }
    .stat-cell:last-child { border-right: none; }
    .stat-cell.highlight { background: #0f172a; }
    .stat-num { font-size: 26px; font-weight: bold; color: #0f172a; line-height: 1; }
    .stat-cell.highlight .stat-num { color: #f7b500; }
    .stat-lbl { font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-top: 4px; }
    .stat-cell.highlight .stat-lbl { color: #cbd5e1; }
    .stat-sub-lbl { font-size: 8px; color: #cbd5e1; margin-top: 1px; }

    /* ── PLAN SECTION HEADER ── */
    .plan-header-table { display: table; width: 100%; margin-top: 20px; margin-bottom: 0; }
    .plan-header-left  { display: table-cell; vertical-align: middle; }
    .plan-header-right { display: table-cell; vertical-align: middle; text-align: right; }

    .plan-title-band {
      padding: 9px 16px;
      background: #1e293b;
      margin-bottom: 0;
    }
    .plan-title-text {
      font-size: 10.5px;
      font-weight: bold;
      color: #ffffff;
      text-transform: uppercase;
      letter-spacing: 0.07em;
    }
    .plan-title-accent { color: #f7b500; }
    .plan-count-pill {
      font-size: 9px;
      font-weight: bold;
      color: #1e293b;
      background: #f7b500;
      padding: 2px 9px;
      border-radius: 99px;
    }

    /* ── VOUCHER TABLE ── */
    .voucher-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .voucher-table thead tr {
      background: #f1f5f9;
      border-bottom: 2px solid #e2e8f0;
    }
    .voucher-table thead th {
      padding: 7px 12px;
      font-size: 8.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      text-align: left;
      color: #64748b;
    }
    .voucher-table thead th.c { text-align: center; }

    .voucher-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    .voucher-table tbody tr:nth-child(odd)  { background: #ffffff; }
    .voucher-table tbody tr:nth-child(even) { background: #fafbfc; }

    .voucher-table tbody td {
      padding: 8px 12px;
      font-size: 11px;
      vertical-align: middle;
    }

    .num-cell   { text-align: center; color: #94a3b8; font-size: 10px; width: 28px; }

    .code-wrap {
      background: #f0f9ff;
      border: 1px dashed #7dd3fc;
      border-radius: 5px;
      padding: 4px 10px;
      display: inline-block;
    }
    .code-text {
      font-family: 'Courier New', Courier, monospace;
      font-size: 13px;
      font-weight: bold;
      letter-spacing: 0.12em;
      color: #0c4a6e;
    }

    .validity-cell { font-size: 9.5px; color: #64748b; }

    .plan-badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 99px;
      font-size: 8.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .badge-diario  { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-semanal { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
    .badge-mensal  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-default { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* ── INSTRUCTIONS BLOCK ── */
    .instructions-block {
      margin-top: 22px;
      border: 1px solid #e2e8f0;
      border-top: 3px solid #f7b500;
    }
    .instructions-header {
      background: #fffbeb;
      padding: 8px 16px;
      font-size: 10px;
      font-weight: bold;
      color: #92400e;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      border-bottom: 1px solid #fde68a;
    }
    .instructions-body {
      padding: 12px 16px;
      background: #ffffff;
    }
    .instr-table { display: table; width: 100%; border-collapse: separate; border-spacing: 0 6px; }
    .instr-row   { display: table-row; }
    .instr-num   {
      display: table-cell;
      width: 22px;
      height: 22px;
      background: #0f172a;
      color: #f7b500;
      font-size: 10px;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
      border-radius: 50%;
      padding-right: 0;
    }
    .instr-text  {
      display: table-cell;
      font-size: 10.5px;
      color: #374151;
      padding-left: 10px;
      vertical-align: middle;
      line-height: 1.5;
    }
    .instr-note {
      margin-top: 10px;
      padding: 8px 12px;
      background: #fef2f2;
      border-left: 3px solid #f87171;
      font-size: 9.5px;
      color: #7f1d1d;
    }

    /* ── FOOTER ── */
    .footer-band {
      margin-top: 22px;
      background: #0f172a;
      padding: 10px 16px;
    }
    .footer-inner  { display: table; width: 100%; }
    .footer-left   { display: table-cell; vertical-align: middle; }
    .footer-right  { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-text   { font-size: 8.5px; color: #64748b; line-height: 1.5; }
    .footer-brand  { font-size: 9px; font-weight: bold; color: #f7b500; }

    @page { margin: 12mm 14mm; }
  </style>
</head>
<body>

  {{-- ════ HEADER BAND ════ --}}
  <div class="header-band">
    <div class="header-inner">
      <div class="header-left">
        <div class="brand-name">Angola<span class="brand-accent">WiFi</span></div>
        <div class="brand-tagline">angolawifi.ao &nbsp;·&nbsp; Vouchers WiFi</div>
      </div>
      <div class="header-right">
        <div class="doc-label">Documento Oficial</div>
        <div class="doc-title">Comprovativo de Venda</div>
        <div class="doc-date">Emitido em {{ $saleDate->format('d/m/Y') }} às {{ $saleDate->format('H:i') }}</div>
      </div>
    </div>
  </div>
  <div class="accent-bar"></div>

  {{-- ════ INFO CARDS ════ --}}
  <div class="info-row">
    <div class="info-card">
      <div class="info-card-label">Revendedor</div>
      <div class="info-card-value">{{ $application->full_name }}</div>
      <div class="info-card-sub">{{ $application->phone ?? $application->email }}</div>
    </div>
    <div class="info-card">
      <div class="info-card-label">Cliente</div>
      <div class="info-card-value">{{ $customerRef ?: 'Não identificado' }}</div>
      <div class="info-card-sub">Cliente final</div>
    </div>
    <div class="info-card">
      <div class="info-card-label">Resumo da Venda</div>
      <div class="info-card-value" style="color:#16a34a;">{{ $totalCodes }} voucher(s)</div>
      <div class="info-card-sub">{{ $codesByPlan->count() }} tipo(s) de plano</div>
    </div>
  </div>

  {{-- ════ STATS BAR ════ --}}
  <div class="stats-row">
    <div class="stat-cell highlight">
      <div class="stat-num">{{ $totalCodes }}</div>
      <div class="stat-lbl">Vouchers Vendidos</div>
    </div>
    @foreach($codesByPlan as $planSlug => $codes)
      @php $plan = $voucherPlans->get($planSlug); @endphp
      <div class="stat-cell">
        <div class="stat-num" style="color:#0f172a;">{{ $codes->count() }}</div>
        <div class="stat-lbl" style="color:#64748b;">{{ $plan ? $plan->name : $planSlug }}</div>
        @if($plan)<div class="stat-sub-lbl" style="color:#94a3b8;">{{ $plan->validity_label }}</div>@endif
      </div>
    @endforeach
  </div>

  {{-- ════ VOUCHER TABLES PER PLAN ════ --}}
  @php $globalIndex = 0; @endphp
  @foreach($codesByPlan as $planSlug => $codes)
    @php
      $plan         = $voucherPlans->get($planSlug);
      $planName     = $plan ? $plan->name : $planSlug;
      $validityLabel= $plan ? $plan->validity_label : '';
      $speedLabel   = $plan ? $plan->speed_label : '';
      $badgeClass   = match($planSlug) {
        'diario'  => 'badge-diario',
        'semanal' => 'badge-semanal',
        'mensal'  => 'badge-mensal',
        default   => 'badge-default',
      };
    @endphp

    {{-- Plan section title --}}
    <div class="plan-title-band">
      <div class="plan-header-table">
        <div class="plan-header-left">
          <span class="plan-title-text">
            <span class="plan-title-accent">&#9632;</span>
            {{ $planName }}
            @if($validityLabel) &mdash; {{ $validityLabel }} @endif
            @if($speedLabel) &nbsp;·&nbsp; {{ $speedLabel }} @endif
          </span>
        </div>
        <div class="plan-header-right">
          <span class="plan-count-pill">{{ $codes->count() }} voucher(s)</span>
        </div>
      </div>
    </div>

    <table class="voucher-table">
      <thead>
        <tr>
          <th class="c" style="width:28px;">#</th>
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
  @endforeach

  {{-- ════ ACTIVATION INSTRUCTIONS ════ --}}
  <div class="instructions-block">
    <div class="instructions-header">Como activar o voucher</div>
    <div class="instructions-body">
      <div class="instr-table">
        <div class="instr-row">
          <div class="instr-num">1</div>
          <div class="instr-text">Ligue-se à rede WiFi <strong>AngolaWiFi</strong> no seu dispositivo.</div>
        </div>
        <div class="instr-row">
          <div class="instr-num">2</div>
          <div class="instr-text">O portal de autenticação será aberto automaticamente no navegador.</div>
        </div>
        <div class="instr-row">
          <div class="instr-num">3</div>
          <div class="instr-text">Introduza o código do voucher no campo indicado.</div>
        </div>
        <div class="instr-row">
          <div class="instr-num">4</div>
          <div class="instr-text">Clique em <strong>"Conectar"</strong> e desfrute da internet.</div>
        </div>
      </div>
      <div class="instr-note">
        <strong>Atenção:</strong> Cada código é de uso único e intransmissível. Uma vez activado, não pode ser reutilizado.
      </div>
    </div>
  </div>

  {{-- ════ FOOTER BAND ════ --}}
  <div class="footer-band">
    <div class="footer-inner">
      <div class="footer-left">
        <div class="footer-brand">AngolaWiFi</div>
        <div class="footer-text">
          Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }}<br>
          Comprovativo de venda ao cliente final — válido sem assinatura
        </div>
      </div>
      <div class="footer-right">
        <div class="footer-text" style="text-align:right;">
          {{ $totalCodes }} voucher(s) vendidos<br>
          Revendedor: <strong style="color:#94a3b8;">{{ $application->full_name }}</strong>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
