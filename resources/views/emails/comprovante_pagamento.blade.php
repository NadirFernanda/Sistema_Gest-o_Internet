<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovativo de Pagamento</title>
</head>
<body style="background:#f8f8f8;padding:0;margin:0;">
    <div style="max-width:520px;margin:40px auto;background:#fff;border-radius:12px;box-shadow:0 2px 8px #0001;padding:32px 32px 24px 32px;">
        <div style="text-align:center;margin-bottom:24px;">
            <img src="{{ asset('img/logo2.jpeg') }}" alt="AgolaWifi Logo" style="max-width:160px;max-height:80px;">
        </div>
        <h2 style="color:#222;text-align:center;margin-bottom:24px;">Olá, <span style="color:#f7b500;">{{ $nomeCliente }}</span>!</h2>
        @if(isset($cobranca) && $cobranca->status === 'pago')
            <p style="font-size:1.1em;color:#333;text-align:center;margin-bottom:24px;">
                <strong>Seu pagamento foi confirmado!</strong><br>
                Segue em anexo o comprovativo de pagamento.
            </p>
        @else
            <p style="font-size:1.1em;color:#333;text-align:center;margin-bottom:24px;">
                Segue em anexo o comprovativo de pagamento referente à sua cobrança.<br>
                Caso tenha dúvidas, entre em contato com o suporte AgolaWifi.
            </p>
        @endif
        <div style="text-align:center;margin-top:32px;color:#888;font-size:0.95em;">
            <strong>AgolaWifi</strong> &copy; {{ date('Y') }}<br>
            Em caso de dúvidas, entre em contato pelo nosso canal de suporte.
        </div>
    </div>
</body>
</html>
