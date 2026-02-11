<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Ficha do Cliente - Minimal</title>
    <style>body{font-family: sans-serif; font-size:12px; padding:12px;}</style>
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
            <li>{{ $pl->nome }} — {{ $pl->estado ?? '-' }}</li>
        @endforeach
        </ul>
    @else
        <p>Nenhum plano.</p>
    @endif
</body>
</html>
