<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Vouchers — {{ $purchase->plan_name }} #{{ $purchase->id }}</title>
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
    .stat-box.blue  { border-color: #bfdbfe; background: #eff6ff; }
    .stat-box.blue  .sv { color: #1d4ed8; }

    /* Table */
    .voucher-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .voucher-table thead tr { background: #1e293b; color: #fff; }
    .voucher-table thead th {
      padding: 7px 10px;
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      text-align: left;
    }
    .voucher-table thead th.center { text-align: center; }
    .voucher-table tbody tr:nth-child(even) { background: #f8fafc; }
    .voucher-table tbody td {
      padding: 6px 10px;
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
    .badge-stock { color: #16a34a; font-weight: bold; }
    .badge-sold  { color: #64748b; }
    .num-cell    { text-align: center; color: #64748b; font-size: 10px; }
    .customer-cell { font-size: 10px; color: #374151; }
    .date-cell     { font-size: 10px; color: #94a3b8; }

    /* Footer */
    .footer {
      border-top: 1px solid #e2e8f0;
      padding-top: 10px;
      color: #94a3b8;
      font-size: 9px;
      display: table;
      width: 100%;
    }
    .footer-left  { display: table-cell; }
    .footer-right { display: table-cell; text-align: right; }

    /* Confidential notice */
    .notice {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-left: 3px solid #f59e0b;
      padding: 7px 10px;
      border-radius: 4px;
      font-size: 9.5px;
      color: #78350f;
      margin-bottom: 14px;
      line-height: 1.5;
    }

    /* Page break */
    @page { margin: 18mm 14mm; }
  </style>
</head>
<body>

  {{-- Header --}}
  <div class="header">
    <div class="header-left">
      <div class="brand">🌐 AngolaWiFi</div>
      <div class="brand-sub">angolawifi.ao · Portal de Revendedores</div>
    </div>
    <div class="header-right">
      <div class="doc-title">Lista de Vouchers</div>
      <div class="doc-sub">Compra #{{ $purchase->id }} · {{ optional($purchase->created_at)->format('d/m/Y H:i') }}</div>
    </div>
  </div>

  {{-- Info grid --}}
  <div class="info-grid">
    <div class="info-cell">
      <div class="info-label">Revendedor</div>
      <div class="info-val">{{ $application->full_name }}</div>
      <div style="font-size:10px;color:#64748b;margin-top:1px;">{{ $application->email }}</div>
    </div>
    <div class="info-cell">
      <div class="info-label">Plano</div>
      <div class="info-val">{{ $purchase->plan_name }}</div>
      @if($voucherPlan)
        <div style="font-size:10px;color:#64748b;margin-top:1px;">{{ $voucherPlan->validity_label }}{{ $voucherPlan->speed_label ? ' · ' . $voucherPlan->speed_label : '' }}</div>
      @endif
    </div>
    <div class="info-cell" style="padding-right:0;">
      <div class="info-label">Lucro potencial</div>
      <div class="info-val green">{{ number_format($purchase->profit_aoa ?? 0, 0, ',', '.') }} Kz</div>
      <div style="font-size:10px;color:#64748b;margin-top:1px;">Investido: {{ number_format($purchase->net_amount_aoa, 0, ',', '.') }} Kz</div>
    </div>
  </div>

  {{-- Stats --}}
  <div class="stats-bar">
    <div class="stat-box">
      <div class="sv">{{ $totalCodes }}</div>
      <div class="sl">Total</div>
    </div>
    <div class="stat-box green">
      <div class="sv">{{ $inStock }}</div>
      <div class="sl">Por vender</div>
    </div>
    <div class="stat-box blue">
      <div class="sv">{{ $distributed }}</div>
      <div class="sl">Vendidos</div>
    </div>
    @if($purchase->profit_aoa && $totalCodes > 0)
    <div class="stat-box green">
      <div class="sv" style="font-size:14px;">{{ number_format($purchase->profit_aoa / $totalCodes, 0, ',', '.') }} Kz</div>
      <div class="sl">Lucro/voucher</div>
    </div>
    @endif
  </div>

  {{-- Notice --}}
  <div class="notice">
    ⚠️ <strong>Confidencial.</strong> Estes códigos destinam-se exclusivamente à revenda autorizada pela AngolaWiFi.
    Os códigos são de uso único. Uma vez utilizados pelo cliente final, não podem ser reinstaurados.
  </div>

  {{-- Voucher table --}}
  <table class="voucher-table">
    <thead>
      <tr>
        <th class="center" style="width:28px;">#</th>
        <th>Código de Voucher</th>
        <th>Estado</th>
        <th>Cliente (ref.)</th>
        <th>Vendido em</th>
      </tr>
    </thead>
    <tbody>
      @foreach($codes as $i => $code)
      <tr>
        <td class="num-cell">{{ $i + 1 }}</td>
        <td class="code-cell">{{ $code->code }}</td>
        <td>
          @if($code->reseller_distributed_at)
            <span class="badge-sold">✓ Vendido</span>
          @else
            <span class="badge-stock">● Disponível</span>
          @endif
        </td>
        <td class="customer-cell">{{ $code->reseller_customer_ref ?: '—' }}</td>
        <td class="date-cell">{{ optional($code->reseller_distributed_at)->format('d/m/Y H:i') ?: '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Footer --}}
  <div class="footer">
    <div class="footer-left">
      AngolaWiFi · Documento gerado em {{ now()->format('d/m/Y \à\s H:i') }} · Uso interno do revendedor
    </div>
    <div class="footer-right">
      Compra #{{ $purchase->id }} · {{ $totalCodes }} voucher(s)
    </div>
  </div>

</body>
</html>
