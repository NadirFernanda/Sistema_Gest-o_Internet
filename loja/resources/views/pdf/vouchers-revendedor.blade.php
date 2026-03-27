<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Vouchers &mdash; {{ $purchase->plan_name }} #{{ $purchase->id }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #333333;
      background: #ffffff;
    }

    /* ── HEADER ── */
    .header {
      background: #f7b500;
      margin-bottom: 0;
    }
    .header-inner { display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; padding: 16px 20px; width: 55%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; padding: 16px 20px; background: #e6a800; }
    .brand     { font-size: 24px; font-weight: bold; color: #1a1a1a; letter-spacing: -0.4px; line-height: 1; }
    .brand-sub { font-size: 9px; color: #4a3a00; margin-top: 3px; letter-spacing: 0.1em; text-transform: uppercase; }
    .doc-type  { font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.14em; color: #4a3a00; margin-bottom: 3px; }
    .doc-title { font-size: 15px; font-weight: bold; color: #1a1a1a; }
    .doc-sub   { font-size: 9px; color: #4a3a00; margin-top: 3px; }
    .header-bar { height: 5px; background: #ffffff; margin-bottom: 20px; }

    /* ── INFO GRID ── */
    .info-grid {
      display: table;
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #e8e8e8;
      margin-bottom: 20px;
    }
    .info-cell {
      display: table-cell;
      width: 33.33%;
      padding: 11px 15px;
      vertical-align: top;
      border-right: 1px solid #e8e8e8;
    }
    .info-cell:last-child { border-right: none; }
    .info-cell.accent { border-top: 3px solid #f7b500; background: #fffdf0; }
    .info-cell.normal { border-top: 3px solid #e8e8e8; background: #fafafa; }
    .info-label { font-size: 7.5px; font-weight: bold; color: #999999; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 4px; }
    .info-val   { font-size: 12px; font-weight: bold; color: #1a1a1a; margin-top: 2px; }
    .info-val.green { color: #16a34a; }
    .info-sub   { font-size: 9px; color: #888888; margin-top: 2px; }

    /* ── STATS BAR ── */
    .stats-bar {
      display: table;
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      background: #f9f9f9;
      border: 1px solid #e8e8e8;
    }
    .stat-cell { display: table-cell; text-align: center; padding: 12px 8px; border-right: 1px solid #e8e8e8; vertical-align: middle; }
    .stat-cell:last-child { border-right: none; }
    .stat-cell.primary { background: #f7b500; }
    .stat-num { font-size: 26px; font-weight: bold; color: #1a1a1a; line-height: 1; }
    .stat-cell.green .stat-num { color: #16a34a; }
    .stat-lbl { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.07em; color: #666666; margin-top: 3px; }
    .stat-cell.primary .stat-lbl { color: #4a3a00; }

    /* ── WARNING NOTICE ── */
    .notice {
      background: #fffbea;
      border: 1px solid #fde68a;
      border-left: 3px solid #f7b500;
      padding: 7px 12px;
      font-size: 9.5px;
      color: #4a3a00;
      margin-bottom: 18px;
      line-height: 1.55;
    }

    /* ── VOUCHER TABLE ── */
    .voucher-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    .voucher-table thead tr { background: #f7b500; }
    .voucher-table thead th {
      padding: 7px 11px;
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      text-align: left;
      color: #1a1a1a;
    }
    .voucher-table thead th.center { text-align: center; }
    .voucher-table tbody tr:nth-child(even) { background: #fafafa; }
    .voucher-table tbody tr:nth-child(odd)  { background: #ffffff; }
    .voucher-table tbody tr { border-bottom: 1px solid #f0f0f0; }
    .voucher-table tbody td {
      padding: 7px 11px;
      font-size: 10.5px;
      vertical-align: middle;
    }

    .num-cell { text-align: center; color: #bbbbbb; font-size: 9px; width: 26px; }

    .code-wrap {
      display: inline-block;
      background: #fffbea;
      border: 1.5px solid #f7b500;
      border-radius: 4px;
      padding: 3px 10px;
    }
    .code-text {
      font-family: 'Courier New', Courier, monospace;
      font-size: 12.5px;
      font-weight: bold;
      letter-spacing: 0.12em;
      color: #333333;
    }

    .badge-stock { color: #16a34a; font-weight: bold; font-size: 10px; }
    .badge-sold  { color: #888888; font-size: 10px; }
    .customer-cell { font-size: 9.5px; color: #555555; }
    .date-cell     { font-size: 9.5px; color: #aaaaaa; }

    /* ── FOOTER ── */
    .footer {
      margin-top: 18px;
      background: #f7b500;
      padding: 10px 18px;
    }
    .footer-inner { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-brand { font-size: 10px; font-weight: bold; color: #1a1a1a; }
    .footer-text  { font-size: 8px; color: #4a3a00; margin-top: 2px; line-height: 1.5; }

    @page { margin: 14mm 14mm; }
  </style>
</head>
<body>

  {{-- ══ HEADER ══ --}}
  <div class="header">
    <div class="header-inner">
      <div class="header-left">
        <div class="brand">AngolaWiFi</div>
        <div class="brand-sub">angolawifi.ao &nbsp;&middot;&nbsp; Portal de Revendedores</div>
      </div>
      <div class="header-right">
        <div class="doc-type">Lista de Vouchers</div>
        <div class="doc-title">{{ $purchase->plan_name }}</div>
        <div class="doc-sub">Compra #{{ $purchase->id }} &middot; {{ optional($purchase->created_at)->format('d/m/Y H:i') }}</div>
      </div>
    </div>
  </div>
  <div class="header-bar"></div>

  {{-- ══ INFO GRID ══ --}}
  <div class="info-grid">
    <div class="info-cell accent">
      <div class="info-label">Revendedor</div>
      <div class="info-val">{{ $application->full_name }}</div>
      <div class="info-sub">{{ $application->email }}</div>
    </div>
    <div class="info-cell normal">
      <div class="info-label">Plano</div>
      <div class="info-val">{{ $purchase->plan_name }}</div>
      @if($voucherPlan)
        <div class="info-sub">{{ $voucherPlan->validity_label }}{{ $voucherPlan->speed_label ? ' &middot; ' . $voucherPlan->speed_label : '' }}</div>
      @endif
    </div>
    <div class="info-cell normal">
      <div class="info-label">Lucro potencial</div>
      <div class="info-val green">{{ number_format($purchase->profit_aoa ?? 0, 0, ',', '.') }} Kz</div>
      <div class="info-sub">Investido: {{ number_format($purchase->net_amount_aoa, 0, ',', '.') }} Kz</div>
    </div>
  </div>

  {{-- ══ STATS BAR ══ --}}
  <div class="stats-bar">
    <div class="stat-cell primary">
      <div class="stat-num">{{ $totalCodes }}</div>
      <div class="stat-lbl">Total</div>
    </div>
    <div class="stat-cell green">
      <div class="stat-num">{{ $inStock }}</div>
      <div class="stat-lbl">Por vender</div>
    </div>
    <div class="stat-cell">
      <div class="stat-num" style="color:#888888;">{{ $distributed }}</div>
      <div class="stat-lbl">Vendidos</div>
    </div>
    @if($purchase->profit_aoa && $totalCodes > 0)
    <div class="stat-cell">
      <div class="stat-num" style="font-size:16px;color:#16a34a;">{{ number_format($purchase->profit_aoa / $totalCodes, 0, ',', '.') }} Kz</div>
      <div class="stat-lbl">Lucro/voucher</div>
    </div>
    @endif
  </div>

  {{-- ══ NOTICE ══ --}}
  <div class="notice">
    <strong>Confidencial.</strong> Estes c&#243;digos destinam-se exclusivamente &#224; revenda autorizada pela AngolaWiFi.
    Os c&#243;digos s&#227;o de uso &#250;nico. Uma vez utilizados pelo cliente final, n&#227;o podem ser reinstaurados.
  </div>

  {{-- ══ VOUCHER TABLE ══ --}}
  <table class="voucher-table">
    <thead>
      <tr>
        <th class="center">#</th>
        <th>C&#243;digo de Voucher</th>
        <th>Estado</th>
        <th>Cliente (ref.)</th>
        <th>Vendido em</th>
      </tr>
    </thead>
    <tbody>
      @foreach($codes as $i => $code)
      <tr>
        <td class="num-cell">{{ $i + 1 }}</td>
        <td>
          <div class="code-wrap">
            <span class="code-text">{{ $code->code }}</span>
          </div>
        </td>
        <td>
          @if($code->reseller_distributed_at)
            <span class="badge-sold">&#10003; Vendido</span>
          @else
            <span class="badge-stock">&#9679; Dispon&#237;vel</span>
          @endif
        </td>
        <td class="customer-cell">{{ $code->reseller_customer_ref ?: '&mdash;' }}</td>
        <td class="date-cell">{{ optional($code->reseller_distributed_at)->format('d/m/Y H:i') ?: '&mdash;' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- ══ FOOTER ══ --}}
  <div class="footer">
    <div class="footer-inner">
      <div class="footer-left">
        <div class="footer-brand">AngolaWiFi</div>
        <div class="footer-text">
          Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }}<br>
          Uso interno do revendedor &mdash; Compra #{{ $purchase->id }}
        </div>
      </div>
      <div class="footer-right">
        <div class="footer-text" style="text-align:right;">
          {{ $totalCodes }} voucher(s)<br>
          Revendedor: <strong>{{ $application->full_name }}</strong>
        </div>
      </div>
    </div>
  </div>

</body>
</html>

