<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Demasiados Pedidos — AngolaWiFi</title>
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
      background: #fee2e2;
      color: #991b1b;
      font-size: .8rem;
      font-weight: 600;
      padding: .3rem .9rem;
      border-radius: 9999px;
      margin-bottom: 1.75rem;
    }
    .wait {
      font-size: 2rem;
      font-weight: 800;
      color: #f59e0b;
      margin: .5rem 0 1.25rem;
    }
    .btn {
      display: inline-block;
      margin-top: .5rem;
      padding: .65rem 1.75rem;
      background: #2563eb;
      color: #fff;
      border-radius: .5rem;
      font-size: .9rem;
      font-weight: 600;
      text-decoration: none;
    }
    .btn:hover { background: #1d4ed8; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">🚦</div>
    <div class="badge">HTTP 429 — Demasiados Pedidos</div>
    <h1>Lentifique o ritmo</h1>
    <p>
      Enviou demasiados pedidos num curto espaço de tempo.
      Por favor aguarde antes de tentar novamente.
    </p>
    @if(!empty($seconds))
      <div class="wait">{{ $seconds }}s</div>
      <p style="font-size:.85rem;color:#475569;">Tempo de espera restante.</p>
    @endif
    <a href="javascript:history.back()" class="btn">← Voltar</a>
  </div>
</body>
</html>
