<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Ficha do Cliente - Minimal</title>
    <style>
        body{font-family: Arial, Helvetica, sans-serif; font-size:13px; padding:12px; color:#222;}
        h2 { text-align:center; margin-bottom:10px; }
        .section-title { font-weight:700; margin:8px 0; }
        ul { padding-left:18px; margin:6px 0 12px 0; }
        li { margin-bottom:6px; }
        .badge { display:inline-block; padding:3px 7px; border-radius:6px; color:#fff; font-size:11px; }
        .badge.plan { background:#ffc107; color:#222; }
        .badge.cobranca-pago { background:#28a745; }
        .badge.cobranca-pendente { background:#ffc107; color:#222; }
    </style>
</head>
<body>
    <h2>Ficha do Cliente</h2>
    {{-- ID removido por solicitação do usuário --}}
    <p><strong>Nome:</strong> {{ $cliente->nome }}</p>
    <p><strong>Contacto:</strong> {{ $cliente->contato ?? '-' }}</p>
    <p><strong>Email:</strong> {{ $cliente->email ?? '-' }}</p>
    <hr>
    <h3>Planos</h3>
    @if($cliente->planos && $cliente->planos->count())
        <ul>
        @foreach($cliente->planos as $pl)
            <li>{{ $pl->nome }} — <span class="badge plan">{{ $pl->estado ?? '-' }}</span></li>
        @endforeach
        </ul>
    @else
        <p>Nenhum plano.</p>
    @endif
</body>
</html>
