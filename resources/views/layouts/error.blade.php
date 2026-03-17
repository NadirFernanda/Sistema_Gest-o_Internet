<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Erro — Sistema de Gestão</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Favicon com caminho absoluto - funciona mesmo sem Vite/assets disponíveis --}}
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,sans-serif;background:#f4f6f8;color:#333;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{background:#fff;border-radius:14px;box-shadow:0 6px 30px rgba(0,0,0,0.08);padding:48px 40px;max-width:540px;width:90%;text-align:center}
        .code{font-size:64px;font-weight:800;color:#e74c3c;line-height:1}
        .title{font-size:22px;font-weight:700;margin:16px 0 10px}
        .msg{color:#555;margin-bottom:28px;line-height:1.6}
        .btn{display:inline-block;padding:10px 22px;background:#0b5ed7;color:#fff;text-decoration:none;border-radius:8px;font-size:15px}
        .btn:hover{background:#0a4fbf}
    </style>
</head>
<body>
    <div class="card">
        @yield('content')
    </div>
</body>
</html>
