<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Factura {{ $invoiceNumber }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 10.5px;
      color: #333333;
      background: #ffffff;
    }

    /* ── HEADER ── */
    .header { background: #f7b500; }
    .header-inner { display: table; width: 100%; }
    .header-left  { display: table-cell; vertical-align: middle; padding: 16px 20px; width: 55%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; padding: 16px 20px; background: #e6a800; }
    .brand     { font-size: 24px; font-weight: bold; color: #1a1a1a; letter-spacing: -0.4px; line-height: 1; }
    .brand-sub { font-size: 9px; color: #4a3a00; margin-top: 3px; letter-spacing: 0.1em; text-transform: uppercase; }
    .doc-type  { font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.14em; color: #4a3a00; margin-bottom: 3px; }
    .doc-title { font-size: 18px; font-weight: bold; color: #1a1a1a; }
    .doc-sub   { font-size: 9px; color: #4a3a00; margin-top: 3px; }
    .header-bar { height: 5px; background: #ffffff; margin-bottom: 16px; }

    /* ── PARTIES (emitente + cliente) ── */
    .parties { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .party-cell { display: table-cell; width: 48%; vertical-align: top; }
    .party-divider { display: table-cell; width: 4%; }
    .party-box { border: 1px solid #e8e8e8; border-top: 3px solid #f7b500; padding: 10px 13px; background: #fffdf0; }
    .party-box.client { border-top-color: #d1d5db; background: #fafafa; }
    .party-label { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.12em; color: #888888; margin-bottom: 6px; }
    .party-name  { font-size: 13px; font-weight: bold; color: #1a1a1a; margin-bottom: 2px; }
    .party-line  { font-size: 9px; color: #555555; line-height: 1.55; }

    /* ── INVOICE META ── */
    .meta-bar { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #f9f9f9; border: 1px solid #e8e8e8; }
    .meta-cell { display: table-cell; padding: 9px 14px; border-right: 1px solid #e8e8e8; vertical-align: middle; }
    .meta-cell:last-child { border-right: none; }
    .meta-label { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; color: #999999; margin-bottom: 3px; }
    .meta-val   { font-size: 11.5px; font-weight: bold; color: #1a1a1a; }

    /* ── ITEMS TABLE ── */
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .items-table thead tr { background: #f7b500; }
    .items-table thead th {
      padding: 7px 11px;
      font-size: 7.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      text-align: left;
      color: #1a1a1a;
    }
    .items-table thead th.right { text-align: right; }
    .items-table tbody tr:nth-child(even) { background: #fafafa; }
    .items-table tbody tr { border-bottom: 1px solid #f0f0f0; }
    .items-table tbody td { padding: 8px 11px; font-size: 10.5px; vertical-align: middle; }
    .items-table tbody td.right { text-align: right; font-weight: 600; }
    .items-table tfoot td { padding: 6px 11px; font-size: 10px; }
    .items-table tfoot tr.subtotal td { border-top: 1px solid #e5e7eb; color: #555; }
    .items-table tfoot tr.tax-row td { color: #dc2626; }
    .items-table tfoot tr.total-row td { border-top: 2px solid #f7b500; font-size: 12px; font-weight: bold; color: #1a1a1a; background: #fffbea; }
    .items-table tfoot td.right { text-align: right; }

    /* ── PAYMENT INFO ── */
    .payment-box { border: 1px solid #e8e8e8; border-left: 3px solid #22c55e; padding: 8px 13px; background: #f0fdf4; margin-bottom: 14px; font-size: 9.5px; color: #166534; line-height: 1.6; }

    /* ── LEGAL NOTE ── */
    .legal { border: 1px solid #fde68a; background: #fffbea; padding: 6px 12px; font-size: 8px; color: #4a3a00; line-height: 1.5; margin-bottom: 14px; }

    /* ── FOOTER ── */
    .footer { background: #f7b500; padding: 10px 18px; }
    .footer-inner { display: table; width: 100%; }
    .footer-left  { display: table-cell; vertical-align: middle; }
    .footer-right { display: table-cell; vertical-align: middle; text-align: right; }
    .footer-brand { font-size: 10px; font-weight: bold; color: #1a1a1a; }
    .footer-text  { font-size: 8px; color: #4a3a00; margin-top: 2px; line-height: 1.5; }

    @page { margin: 14mm 14mm; }
  </style>
</head>
<body>

  {{-- HEADER --}}
  <div class="header">
    <div class="header-inner">
      <div class="header-left">
        <div class="brand">AngolaWiFi</div>
        <div class="brand-sub">angolawifi.ao &nbsp;&middot;&nbsp; Portal de Revendedores</div>
      </div>
      <div class="header-right">
        <div class="doc-type">Documento Comercial</div>
        <div class="doc-title">Factura</div>
        <div class="doc-sub">{{ $invoiceNumber }}</div>
      </div>
    </div>
  </div>
  <div class="header-bar"></div>

  {{-- PARTIES --}}
  <div class="parties">
    <div class="party-cell">
      <div class="party-box">
        <div class="party-label">Emitente</div>
        <div class="party-name">AngolaWiFi</div>
        <div class="party-line">
          angolawifi.ao<br>
          suporte@angolawifi.ao
        </div>
      </div>
    </div>
    <div class="party-divider"></div>
    <div class="party-cell">
      <div class="party-box client">
        <div class="party-label">Cliente (Agente Revendedor)</div>
        <div class="party-name">{{ $application->full_name }}</div>
        <div class="party-line">
          @if($application->document_number)NIF: {{ $application->document_number }}<br>@endif
          {{ $application->email }}<br>
          @if($application->phone){{ $application->phone }}<br>@endif
          @if($application->address){{ $application->address }}@endif
        </div>
      </div>
    </div>
  </div>

  {{-- META --}}
  <div class="meta-bar">
    <div class="meta-cell">
      <div class="meta-label">Nº Factura</div>
      <div class="meta-val">{{ $invoiceNumber }}</div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Data de Emissão</div>
      <div class="meta-val">{{ $invoiceDate }}</div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Ref. Pagamento</div>
      <div class="meta-val">{{ $purchase->payment_reference ?? 'N/D' }}</div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Método</div>
      <div class="meta-val">{{ $paymentMethodLabel }}</div>
    </div>
  </div>

  {{-- ITEMS TABLE --}}
  <table class="items-table">
    <thead>
      <tr>
        <th>Descri&ccedil;&atilde;o</th>
        <th class="right">Qtd.</th>
        <th class="right">Pre&ccedil;o Unit. (Kz)</th>
        <th class="right">Pre&ccedil;o P&uacute;blico (Kz)</th>
        <th class="right">Total (Kz)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($purchases as $p)
      <tr>
        <td>
          Vouchers WiFi &mdash; {{ $p->plan_name }}
          @php $plan = $plans->get($p->plan_slug); @endphp
          @if($plan)
            <br><span style="font-size:8.5px;color:#888;">{{ $plan->validity_label }} &middot; {{ $plan->speed_label }}</span>
          @endif
        </td>
        <td class="right">{{ $p->quantity }}</td>
        <td class="right">{{ number_format($p->unit_price_aoa, 0, ',', '.') }}</td>
        <td class="right">{{ number_format($p->gross_amount_aoa, 0, ',', '.') }}</td>
        <td class="right">{{ number_format($p->net_amount_aoa, 0, ',', '.') }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="subtotal">
        <td colspan="4" class="right" style="color:#555;font-size:9.5px;">Valor Bruto (Pre&ccedil;o P&uacute;blico Total)</td>
        <td class="right">{{ number_format($grossTotal, 0, ',', '.') }} Kz</td>
      </tr>
      <tr class="subtotal">
        <td colspan="4" class="right" style="color:#555;font-size:9.5px;">Desconto Agente ({{ $discountPct }}%)</td>
        <td class="right" style="color:#16a34a;">- {{ number_format($discountAmount, 0, ',', '.') }} Kz</td>
      </tr>
      <tr class="tax-row">
        <td colspan="4" class="right" style="font-size:9.5px;">IRT Retido (6,5% sobre lucro bruto)</td>
        <td class="right">+ {{ number_format($taxTotal, 0, ',', '.') }} Kz</td>
      </tr>
      <tr class="total-row">
        <td colspan="4" class="right">TOTAL A PAGAR</td>
        <td class="right">{{ number_format($netTotal, 0, ',', '.') }} Kz</td>
      </tr>
    </tfoot>
  </table>

  {{-- PAYMENT CONFIRMATION --}}
  <div class="payment-box">
    &#10003; <strong>Pagamento recebido</strong> em {{ $paidAt }} via {{ $paymentMethodLabel }}.
    @if($purchase->payment_reference) Ref: <strong>{{ $purchase->payment_reference }}</strong>.@endif
    {{ $purchases->sum('codes_count') }} voucher(s) transferido(s) para a conta do agente.
  </div>

  {{-- LEGAL NOTE --}}
  <div class="legal">
    <strong>Nota fiscal:</strong> O presente documento tem car&aacute;cter informativo. A AngolaWiFi reserva-se o direito de emitir documenta&ccedil;&atilde;o fiscal complementar nos termos da legisla&ccedil;&atilde;o vigor&aacute;vel.
    IRT retido na fonte nos termos do C&oacute;digo do IRT em Angola.
  </div>

  {{-- FOOTER --}}
  <div class="footer">
    <div class="footer-inner">
      <div class="footer-left">
        <div class="footer-brand">AngolaWiFi</div>
        <div class="footer-text">
          Emitido em {{ now()->format('d/m/Y \à\s H:i') }}<br>
          {{ $invoiceNumber }} &mdash; Agente: {{ $application->full_name }}
        </div>
      </div>
      <div class="footer-right">
        <div class="footer-text" style="text-align:right;">
          Total pago: <strong>{{ number_format($netTotal, 0, ',', '.') }} Kz</strong><br>
          {{ $purchases->sum('codes_count') }} voucher(s) transferido(s)
        </div>
      </div>
    </div>
  </div>

</body>
</html>
