<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $application->status === 'approved' ? 'Candidatura aprovada' : 'Candidatura não aprovada' }} — AngolaWiFi</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{background:#f1f5f9;font-family:Inter,Arial,sans-serif;color:#1a202c;}
    .wrap{max-width:560px;margin:2rem auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
    .header{background:linear-gradient(135deg,#f7b500,#ff9500);padding:2rem 2rem 1.5rem;text-align:center;}
    .header img{height:44px;margin-bottom:.75rem;}
    .header-title{font-size:1.25rem;font-weight:800;color:#1a202c;letter-spacing:-.02em;}
    .body{padding:2rem;}
    .status-badge{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.1rem;border-radius:999px;font-size:.85rem;font-weight:700;margin-bottom:1.25rem;}
    .badge-approved{background:#dcfce7;color:#15803d;}
    .badge-rejected{background:#fee2e2;color:#b91c1c;}
    .badge-icon{width:18px;height:18px;}
    h2{font-size:1.15rem;font-weight:800;margin-bottom:.6rem;color:#1a202c;}
    p{font-size:.9rem;line-height:1.7;color:#374151;margin-bottom:.85rem;}
    .info-box{background:#f8fafc;border-left:4px solid #f7b500;border-radius:0 8px 8px 0;padding:.85rem 1rem;margin:1.25rem 0;font-size:.88rem;color:#374151;}
    .info-box strong{color:#1a202c;}
    .steps{margin:1.25rem 0;}
    .steps p{font-size:.77rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:.5rem;}
    .steps ol{padding-left:1.25rem;font-size:.88rem;color:#374151;line-height:2;}
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
  </div>

  <div class="body">

    @if($application->status === 'approved')
      <span class="status-badge badge-approved">
        <svg class="badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        Candidatura Aprovada
      </span>
      <h2>Parabéns, {{ $application->full_name }}!</h2>
      <p>Temos o prazer de informar que a sua candidatura para ser agente revendedor AngolaWiFi foi <strong>aprovada</strong>.</p>
      <p>Bem-vindo à nossa rede de parceiros! Pode agora aceder ao Painel do Revendedor e começar a comprar códigos Wi-Fi com as suas condições de desconto.</p>

      <div class="info-box">
        <strong>O que precisa de fazer agora:</strong><br>
        Aceda ao Painel do Revendedor em <a href="{{ url('/painel-revendedor') }}">angolawifi.ao/painel-revendedor</a> com o seu número de telefone registado (<strong>{{ $application->phone }}</strong>) e siga as instruções de acesso.
      </div>

      <div class="steps">
        <p>Próximos passos</p>
        <ol>
          <li>Aceder ao Painel do Revendedor</li>
          <li>Seleccionar o pacote de códigos pretendido</li>
          <li>Efectuar o pagamento e receber os códigos imediatamente</li>
          <li>Revender os códigos aos seus clientes com margem de lucro</li>
        </ol>
      </div>

      <div class="cta">
        <a href="{{ url('/painel-revendedor') }}">Aceder ao Painel do Revendedor</a>
      </div>

    @else
      <span class="status-badge badge-rejected">
        <svg class="badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Candidatura Não Aprovada
      </span>
      <h2>Olá, {{ $application->full_name }}</h2>
      <p>Após análise cuidadosa, informamos que a sua candidatura para agente revendedor AngolaWiFi <strong>não foi aprovada</strong> neste momento.</p>
      <p>Agradecemos o seu interesse no nosso programa de revenda e o tempo despendido no preenchimento do formulário.</p>

      <div class="info-box">
        Pode voltar a candidatar-se no futuro se as suas circunstâncias mudarem. Para mais informações, entre em contacto connosco.
      </div>

      <div class="cta">
        <a href="{{ url('/quero-ser-revendedor') }}">Saber mais sobre o programa</a>
      </div>
    @endif

    <p style="font-size:.82rem;color:#94a3b8;margin-top:1rem;">Se tiver alguma questão, responda a este e-mail ou contacte-nos directamente.</p>
  </div>

  <div class="footer">
    <p>AngolaWiFi &mdash; Internet acess&iacute;vel para todos</p>
    <p style="margin-top:.3rem;"><a href="{{ url('/') }}">angolawifi.ao</a></p>
  </div>

</div>
</body>
</html>
