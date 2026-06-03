<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Confirmação de Instalação — AngolaWiFi</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{background:#f1f5f9;font-family:Inter,Arial,sans-serif;color:#1a202c;}
    .wrap{max-width:560px;margin:2rem auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
    .header{background:linear-gradient(135deg,#f7b500,#ff9500);padding:2rem 2rem 1.5rem;text-align:center;}
    .header-title{font-size:1.25rem;font-weight:800;color:#1a202c;letter-spacing:-.02em;}
    .header-sub{font-size:.85rem;color:rgba(26,32,44,.65);margin-top:.3rem;}
    .body{padding:2rem;}
    .status-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;border-radius:999px;font-size:.85rem;font-weight:700;margin-bottom:1.25rem;background:#dcfce7;color:#15803d;}
    h2{font-size:1.1rem;font-weight:800;margin-bottom:.6rem;color:#1a202c;}
    p{font-size:.9rem;line-height:1.7;color:#374151;margin-bottom:.85rem;}
    .info-box{background:#fffbeb;border-left:4px solid #f7b500;border-radius:0 8px 8px 0;padding:.85rem 1rem;margin:1.25rem 0;font-size:.88rem;color:#374151;line-height:1.65;}
    .info-box strong{color:#1a202c;display:block;margin-bottom:.3rem;}
    .detail-row{display:flex;justify-content:space-between;align-items:baseline;padding:.45rem 0;border-bottom:1px solid #f1f5f9;font-size:.875rem;}
    .detail-row:last-child{border-bottom:none;}
    .detail-label{color:#64748b;font-size:.8rem;}
    .detail-val{font-weight:600;color:#1a202c;}
    .steps{margin:1.25rem 0;}
    .steps-title{font-size:.77rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.5rem;}
    .steps ol{padding-left:1.25rem;font-size:.88rem;color:#374151;line-height:2.1;}
    .attach-note{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.75rem 1rem;font-size:.83rem;color:#64748b;margin:1.25rem 0;display:flex;align-items:flex-start;gap:.65rem;}
    .attach-icon{font-size:1.2rem;flex-shrink:0;}
    .cta{text-align:center;margin:1.5rem 0 .5rem;}
    .cta a{display:inline-block;padding:.7rem 2rem;background:linear-gradient(135deg,#f7b500,#ff9500);color:#1a202c;font-weight:800;font-size:.9rem;border-radius:999px;text-decoration:none;}
    .footer{background:#f8fafc;border-top:1px solid #e2e8f0;padding:1.25rem 2rem;text-align:center;font-size:.78rem;color:#94a3b8;line-height:1.7;}
    .footer a{color:#f7b500;text-decoration:none;}
  </style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <div class="header-title">AngolaWiFi</div>
    <div class="header-sub">Confirmação de pedido de instalação</div>
  </div>

  <div class="body">
    <span class="status-badge">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      Pedido Confirmado
    </span>

    <h2>Olá, {{ $appointment->name }}!</h2>
    <p>
      O seu pedido de instalação do serviço de internet AngolaWiFi foi <strong>confirmado</strong>.
      A nossa equipa técnica irá contactá-lo(a) brevemente para agendar a data e hora da instalação.
    </p>

    <div class="info-box">
      <strong>Resumo do pedido</strong>
      <div class="detail-row">
        <span class="detail-label">Nome</span>
        <span class="detail-val">{{ $appointment->name }}</span>
      </div>
      <div class="detail-row">
        <span class="detail-label">Telefone</span>
        <span class="detail-val">{{ $appointment->phone }}</span>
      </div>
      @if($appointment->morada)
      <div class="detail-row">
        <span class="detail-label">Morada</span>
        <span class="detail-val">{{ $appointment->morada }}</span>
      </div>
      @endif
      <div class="detail-row">
        <span class="detail-label">Tipo de serviço</span>
        <span class="detail-val">{{ \App\Models\InstallationAppointment::typeLabel($appointment->type) }}</span>
      </div>
    </div>

    <div class="steps">
      <p class="steps-title">Próximos passos</p>
      <ol>
        <li>A nossa equipa contacta-o(a) para confirmar data e hora</li>
        <li>Técnico desloca-se à sua morada para a instalação</li>
        <li>Efectua o pagamento da taxa de instalação (125.450 Kz)</li>
        <li>Serviço de internet activado na hora</li>
      </ol>
    </div>

    <div class="attach-note">
      <span class="attach-icon">📄</span>
      <span>
        <strong style="color:#1a202c;display:block;margin-bottom:.2rem;">Contrato em anexo</strong>
        Este e-mail inclui em anexo o <strong>Contrato de Comodato de Equipamento com Serviço de Internet</strong>.
        Por favor leia-o com atenção — o pagamento da taxa de instalação constitui aceitação integral do contrato
        (Cláusula 12.ª).
      </span>
    </div>

    <div class="cta">
      <a href="tel:+244949364505">Contactar Suporte: +244 949 364 505</a>
    </div>

    <p style="font-size:.82rem;color:#94a3b8;margin-top:1rem;">
      Se tiver alguma questão, responda a este e-mail ou contacte-nos directamente em
      <a href="mailto:suporte@angolawifi.ao" style="color:#d97706;">suporte@angolawifi.ao</a>.
    </p>
  </div>

  <div class="footer">
    <p>AngolaWiFi &mdash; Internet acess&iacute;vel para todos</p>
    <p style="margin-top:.3rem;"><a href="{{ url('/') }}">angolawifi.ao</a></p>
  </div>

</div>
</body>
</html>
