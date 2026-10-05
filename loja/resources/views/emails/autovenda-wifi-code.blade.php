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

    @if($order->bonus_wifi_code)
        <h2>O seu bónus promocional: {{ $order->bonus_plan_name }} ({{ $order->bonus_plan_validity }})</h2>
        <p><strong>Código de acesso WiFi gratuito:</strong> <code>{{ $order->bonus_wifi_code }}</code></p>
        <p>Este código promocional é adicional ao plano comprado.</p>
    @elseif($order->bonus_delivery_status === 'stock_unavailable')
        <p>A sua compra inclui um bónus promocional. A equipa AngolaWiFi está a verificar a entrega do código e entrará em contacto consigo.</p>
    @endif

    <p>Em caso de dúvidas, contacte o suporte AngolaWiFi.</p>
</body>
</html>
