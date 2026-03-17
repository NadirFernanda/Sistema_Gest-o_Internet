<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Código de verificação AngolaWiFi</title>
  <style>
    body { margin:0; padding:0; background:#f4f6f9; font-family:Inter,Arial,sans-serif; color:#1a202c; }
    .wrap { max-width:520px; margin:2rem auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); }
    .header { background:#0f172a; padding:1.5rem 2rem; text-align:center; }
    .header img { height:40px; }
    .header-title { color:#f7b500; font-size:1.1rem; font-weight:800; margin-top:.5rem; }
    .body { padding:2rem 2rem 1.5rem; }
    .body h2 { font-size:1.2rem; font-weight:800; margin:0 0 .75rem; }
    .body p { font-size:.95rem; color:#475569; line-height:1.6; margin:.5rem 0; }
    .otp-box { background:#f8fafc; border:2px dashed #f7b500; border-radius:10px; text-align:center; padding:1.5rem 1rem; margin:1.5rem 0; }
    .otp-code { font-size:2.5rem; font-weight:900; letter-spacing:.3em; color:#0f172a; font-family:monospace; }
    .otp-note { font-size:.8rem; color:#94a3b8; margin-top:.5rem; }
    .footer { background:#f8fafc; padding:1rem 2rem; font-size:.78rem; color:#94a3b8; text-align:center; border-top:1px solid #e2e8f0; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header">
      <div class="header-title">AngolaWiFi — Área Pessoal</div>
    </div>
    <div class="body">
      <h2>Código de verificação</h2>
      <p>Recebemos um pedido de acesso à sua área pessoal em <strong>angolawifi.ao</strong>.</p>
      <p>Introduza o código abaixo no formulário de verificação:</p>

      <div class="otp-box">
        <div class="otp-code">{{ $otp }}</div>
        <div class="otp-note">⏱ Válido durante <strong>10 minutos</strong></div>
      </div>

      <p>Se não pediu este código, ignore este email. A sua conta não foi acedida.</p>
      <p style="font-size:.85rem;color:#94a3b8;">Por segurança, nunca partilhe este código com ninguém, incluindo a equipa AngolaWiFi.</p>
    </div>
    <div class="footer">
      AngolaWiFi · Este email foi gerado automaticamente, não responda.
    </div>
  </div>
</body>
</html>
