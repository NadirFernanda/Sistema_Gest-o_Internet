<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <title>Factura Taxa de Manutena&ccedil;&atilde;o {{ $invoiceNumber }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 10.5px;
      color: #333333;
      background: #ffffff;
    }

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

    .parties { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .party-cell { display: table-cell; width: 48%; vertical-align: top; }
    .party-divider { display: table-cell; width: 4%; }
    .party-box { border: 1px solid #e8e8e8; border-top: 3px solid #f7b500; padding: 10px 13px; background: #fffdf0; }
    .party-box.client { border-top-color: #d1d5db; background: #fafafa; }
    .party-label { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.12em; color: #888888; margin-bottom: 6px; }
    .party-name  { font-size: 13px; font-weight: bold; color: #1a1a1a; margin-bottom: 2px; }
    .party-line  { font-size: 9px; color: #555555; line-height: 1.55; }

    .meta-bar { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; background: #f9f9f9; border: 1px solid #e8e8e8; }
    .meta-cell { display: table-cell; padding: 9px 14px; border-right: 1px solid #e8e8e8; vertical-align: middle; }
    .meta-cell:last-child { border-right: none; }
    .meta-label { font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; color: #999999; margin-bottom: 3px; }
    .meta-val   { font-size: 11.5px; font-weight: bold; color: #1a1a1a; }

    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .items-table thead tr { background: #f7b500; }
    .items-table thead th {
      padding: 7px 11px; font-size: 7.5px; font-weight: bold;
      text-transform: uppercase; letter-spacing: 0.08em; text-align: left; color: #1a1a1a;
    }
    .items-table thead th.right { text-align: right; }
    .items-table tbody tr { border-bottom: 1px solid #f0f0f0; }
    .items-table tbody td { padding: 10px 11px; font-size: 10.5px; vertical-align: middle; }
    .items-table tbody td.right { text-align: right; font-weight: 600; }
    .items-table tfoot tr.total-row td { border-top: 2px solid #f7b500; font-size: 12px; font-weight: bold; color: #1a1a1a; background: #fffbea; padding: 8px 11px; }
    .items-table tfoot td.right { text-align: right; }

    .payment-box { border: 1px solid #e8e8e8; border-left: 3px solid #22c55e; padding: 8px 13px; background: #f0fdf4; margin-bottom: 14px; font-size: 9.5px; color: #166534; line-height: 1.6; }
    .legal { border: 1px solid #fde68a; background: #fffbea; padding: 6px 12px; font-size: 8px; color: #4a3a00; line-height: 1.5; margin-bottom: 14px; }

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

  <div class="parties">
    <div class="party-cell">
      <div class="party-box">
        <div class="party-label">Emitente</div>
        <div class="party-name">AngolaWiFi</div>
        <div class="party-line">angolawifi.ao<br>suporte@angolawifi.ao</div>
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

  <div class="meta-bar">
    <div class="meta-cell">
      <div class="meta-label">N&ordm; Factura</div>
      <div class="meta-val">{{ $invoiceNumber }}</div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Per&iacute;odo</div>
      <div class="meta-val">{{ $periodLabel }}</div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Data de Emiss&atilde;o</div>
      <div class="meta-val">{{ $invoiceDate }}</div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">M&eacute;todo</div>
      <div class="meta-val">{{ $paymentMethodLabel }}</div>
    </div>
  </div>

  <table class="items-table">
    <thead>
      <tr>
        <th>Descri&ccedil;&atilde;o</th>
        <th class="right">Valor (Kz)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          Taxa de Manuten&ccedil;&atilde;o Mensal &mdash; {{ $periodLabel }}<br>
          <span style="font-size:8.5px;color:#888;">
            Modo {{ $modeLabel }} &mdash; {{ $application->full_name }}
          </span>
        </td>
        <td class="right">{{ number_format($amount, 0, ',', '.') }}</td>
      </tr>
    </tbody>
    <tfoot>
      <tr class="total-row">
        <td class="right">TOTAL PAGO</td>
        <td class="right">{{ number_format($amount, 0, ',', '.') }} Kz</td>
      </tr>
    </tfoot>
  </table>

  <div class="payment-box">
    &#10003; <strong>Pagamento recebido</strong> em {{ $paidAt }} via {{ $paymentMethodLabel }}.
    @if($reference) Ref: <strong>{{ $reference }}</strong>.@endif
  </div>

  <div class="legal">
    <strong>Nota fiscal:</strong> O presente documento tem car&aacute;cter informativo. A AngolaWiFi reserva-se o direito de emitir documenta&ccedil;&atilde;o fiscal complementar nos termos da legisla&ccedil;&atilde;o vigor&aacute;vel.
  </div>

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
          Per&iacute;odo: <strong>{{ $periodLabel }}</strong><br>
          Valor: <strong>{{ number_format($amount, 0, ',', '.') }} Kz</strong>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
