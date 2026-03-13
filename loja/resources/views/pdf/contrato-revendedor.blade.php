<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Agente Revendedor</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 13px; color: #222; }
        h1 { font-size: 1.3rem; text-align: center; margin-bottom: 1.5rem; }
        .section { margin-bottom: 1.2rem; }
        .label { font-weight: bold; }
        .assinatura { margin-top: 2.5rem; }
    </style>
</head>
<body>
    <h1>CONTRATO DE AGENTE REVENDEDOR</h1>
    <div class="section">
        <span class="label">Nome do Agente:</span> {{ $application->full_name }}<br>
        <span class="label">Nº do BI/NIF:</span> {{ $application->document_number }}<br>
        <span class="label">Morada:</span> {{ $application->address }}<br>
        <span class="label">E-mail:</span> {{ $application->email }}<br>
        <span class="label">Telefone:</span> {{ $application->phone }}<br>
        <span class="label">Local de Instalação:</span> {{ $application->installation_location }}<br>
        <span class="label">Tipo de Internet:</span> {{ $application->internet_type === 'own' ? 'Tem internet própria' : 'Necessita de internet fornecida pela AngolaWiFi' }}
    </div>
    <div class="section">
        <p>Por este instrumento particular, as partes acima identificadas firmam o presente Contrato de Agente Revendedor, regendo-se pelas cláusulas e condições seguintes:</p>
        <ol>
            <li>O agente compromete-se a comercializar os serviços/produtos AngolaWiFi conforme as regras e preços definidos pela empresa.</li>
            <li>O agente terá acesso a descontos progressivos conforme o volume de compras.</li>
            <li>O agente deverá prestar informações corretas aos clientes finais e zelar pela boa imagem da marca.</li>
            <li>O presente contrato tem validade de 12 meses, renovável automaticamente salvo manifestação em contrário.</li>
            <li>O não cumprimento das cláusulas poderá resultar na rescisão imediata do contrato.</li>
        </ol>
    </div>
    <div class="assinatura">
        <div style="float:left;width:45%;text-align:center;">
            ___________________________<br>
            <span class="label">Agente Revendedor</span><br>
            {{ $application->full_name }}
        </div>
        <div style="float:right;width:45%;text-align:center;">
            ___________________________<br>
            <span class="label">AngolaWiFi</span><br>
        </div>
        <div style="clear:both;"></div>
    </div>
    <div class="section" style="margin-top:2.5rem;font-size:0.95em;color:#666;">
        Data de geração: {{ date('d/m/Y') }}
    </div>
</body>
</html>
