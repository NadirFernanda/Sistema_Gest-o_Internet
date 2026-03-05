<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Novo pedido de revenda AngolaWiFi</title>
</head>
<body>
    <p>Foi submetido um novo pedido para ser agente revendedor AngolaWiFi.</p>

    <p><strong>Nome:</strong> {{ $application->full_name }}</p>
    <p><strong>BI/NIF:</strong> {{ $application->document_number }}</p>
    <p><strong>Morada:</strong> {{ $application->address }}</p>
    <p><strong>E-mail:</strong> {{ $application->email }}</p>
    <p><strong>Telefone/WhatsApp:</strong> {{ $application->phone }}</p>
    <p><strong>Local de Instalação Pretendido:</strong> {{ $application->installation_location }}</p>

    <p><strong>Assunto:</strong> {{ $application->subject }}</p>
    <p><strong>Mensagem:</strong></p>
    <p>{{ $application->message }}</p>
</body>
</html>
