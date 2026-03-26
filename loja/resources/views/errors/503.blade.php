<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="{{ $retryAfter ?? 10 }}">
  <title>Servidor Ocupado — AngolaWiFi</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #0f172a;
      color: #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 2rem;
    }
    .card {
      background: #1e293b;
      border: 1px solid #334155;
      border-radius: 1rem;
      padding: 3rem 2.5rem;
      max-width: 480px;
      width: 100%;
      text-align: center;
      box-shadow: 0 25px 50px rgba(0,0,0,.4);
    }
    .icon { font-size: 3.5rem; margin-bottom: 1.25rem; }
    h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #f1f5f9;
      margin-bottom: .75rem;
    }
    p {
      font-size: .95rem;
      color: #94a3b8;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }
    .badge {
      display: inline-block;
      background: #fef3c7;
      color: #92400e;
      font-size: .8rem;
      font-weight: 600;
      padding: .3rem .9rem;
      border-radius: 9999px;
      margin-bottom: 1.75rem;
    }
    .counter {
      font-size: .82rem;
      color: #64748b;
      margin-top: 1.5rem;
    }
    .counter span { color: #f59e0b; font-weight: 700; }
    .btn {
      display: inline-block;
      margin-top: 1.25rem;
      padding: .65rem 1.75rem;
      background: #2563eb;
      color: #fff;
      border-radius: .5rem;
      font-size: .9rem;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      border: none;
    }
    .btn:hover { background: #1d4ed8; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">⚙️</div>
    <div class="badge">HTTP 503 — Serviço Temporariamente Indisponível</div>
    <h1>Servidor Ocupado</h1>
    <p>
      O sistema está neste momento a processar um volume elevado de pedidos.
      Não foi necessário fazer nada — a página será recarregada automaticamente
      em alguns segundos.
    </p>
    <p style="font-size:.85rem;color:#475569;">
      Se o problema persistir por mais de 1 minuto, por favor contacte o suporte da AngolaWiFi.
    </p>
    <button class="btn" onclick="location.reload()">Tentar agora</button>
    <div class="counter">A recarregar automaticamente em <span id="cd">{{ $retryAfter ?? 10 }}</span> segundos…</div>
  </div>
  <script>
    let s = {{ $retryAfter ?? 10 }};
    const el = document.getElementById('cd');
    const t = setInterval(() => { s--; el.textContent = s; if (s <= 0) clearInterval(t); }, 1000);
  </script>
</body>
</html>
