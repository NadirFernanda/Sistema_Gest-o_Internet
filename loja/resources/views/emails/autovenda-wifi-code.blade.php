<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Seu código AngolaWiFi</title>
</head>
<body>
    <p>Saudações, {{ $order->customer_name }}.</p>

    <p>Obrigado por comprar AngolaWiFi.</p>

    <p><strong>Plano:</strong> {{ $order->plan_name }}</p>
    <p><strong>Código de acesso WiFi:</strong> <code>{{ $order->wifi_code }}</code></p>

    <p>Use este código na rede AngolaWiFi para ativar o seu acesso.</p>

    <p>Em caso de dúvidas, contacte o suporte AngolaWiFi.</p>
</body>
</html>
