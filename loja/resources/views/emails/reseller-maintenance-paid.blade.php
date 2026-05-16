<!DOCTYPE html>
<html lang="pt-PT">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Taxa de manutenção confirmada — AngolaWiFi</title>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{background:#f1f5f9;font-family:Inter,Arial,sans-serif;color:#1a202c;}
    .wrap{max-width:560px;margin:2rem auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
    .header{background:linear-gradient(135deg,#f7b500,#ff9500);padding:2rem 2rem 1.5rem;text-align:center;}
    .header-title{font-size:1.25rem;font-weight:800;color:#1a202c;letter-spacing:-.02em;}
    .body{padding:2rem;}
    h2{font-size:1.15rem;font-weight:800;margin-bottom:.75rem;color:#1a202c;}
    p{font-size:.9rem;line-height:1.75;color:#374151;margin-bottom:.85rem;}
    .highlight-box{background:#f0fdf4;border-left:4px solid #16a34a;border-radius:0 8px 8px 0;padding:1rem 1.1rem;margin:1.25rem 0;}
    .highlight-box .amount{font-size:1.35rem;font-weight:800;color:#15803d;display:block;margin-bottom:.2rem;}
    .highlight-box p{margin:0;font-size:.88rem;color:#166534;}
    .cta{text-align:center;margin:1.5rem 0 .25rem;}
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
    <h2>Prezado(a) {{ $application->full_name }},</h2>

    <p>Parabéns por continuar connosco e fazer parte da nossa rede de parceiros.</p>

    @if($bonusAoa > 0)
    <p>Informamos que os valores pagos foram devidamente compensados com recargas/vouchers no valor de:</p>

    <div class="highlight-box">
      <span class="amount">Kz {{ number_format($bonusAoa, 2, ',', '.') }}</span>
      <p>já disponíveis para utilização no seu painel de revendedor.</p>
    </div>
    @endif

    <p>Agradecemos pela confiança, preferência e parceria contínua.</p>

    <div class="cta">
      <a href="{{ url('/painel-revendedor') }}">Aceder ao Painel do Revendedor</a>
    </div>

    <p style="font-size:.82rem;color:#94a3b8;margin-top:1.25rem;">
      Atenciosamente,<br>
      <strong style="color:#374151;">Equipa AngolaWiFi</strong>
    </p>
  </div>

  <div class="footer">
    <p>AngolaWiFi &mdash; Internet acess&iacute;vel para todos</p>
    <p style="margin-top:.3rem;"><a href="{{ url('/') }}">angolawifi.ao</a></p>
  </div>

</div>
</body>
</html>
