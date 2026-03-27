<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<title>Alerta da sua conta AngolaWiFi</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 15px; color: #1e293b; line-height: 1.6; }
  .container { max-width: 560px; margin: 0 auto; padding: 24px; }
  .alert-box { border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
  .alert-maintenance { background: #fee2e2; border-left: 4px solid #dc2626; }
  .alert-target { background: #fef3c7; border-left: 4px solid #f59e0b; }
  .amount { font-size: 1.25rem; font-weight: 700; }
  .footer { font-size: .85rem; color: #64748b; margin-top: 24px; }
</style>
</head>
<body>
<div class="container">

  <p>Saudações, <strong>{{ $application->full_name }}</strong>,</p>

  @if($alertType === 'maintenance')
    <div class="alert-box alert-maintenance">
      <p>⚠️ <strong>A sua taxa de manutenção mensal está em atraso.</strong></p>
      <p>
        Valor a pagar:
        <span class="amount">{{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz</span>
      </p>
      <p>
        Por favor, proceda ao pagamento da taxa de manutenção o mais brevemente possível
        para regularizar a sua situação como agente revendedor AngolaWiFi.
      </p>
    </div>
    <p>
      Após realizar o pagamento, envie o comprovativo para o seu gestor de conta ou
      contacte-nos através dos canais habituais para confirmarmos a regularização.
    </p>

  @elseif($alertType === 'target')
    <div class="alert-box alert-target">
      <p>⚠️ <strong>A meta mensal de compras ainda não foi atingida.</strong></p>
      @php
        $spend     = $application->monthlySpendings();
        $target    = $application->monthly_target_aoa;
        $remaining = max(0, $target - $spend);
      @endphp
      <p>
        Meta: <span class="amount">{{ number_format($target, 0, ',', '.') }} Kz</span><br>
        Já comprou: <strong>{{ number_format($spend, 0, ',', '.') }} Kz</strong><br>
        Em falta: <strong>{{ number_format($remaining, 0, ',', '.') }} Kz</strong>
      </p>
    </div>
    <p>
      Recorde-se de que atingir a meta mensal é um requisito do seu contrato de revenda.
      Ainda tem até ao final do mês para completar as suas compras de vouchers.
    </p>
  @endif

  <p>
    Para comprar vouchers basta aceder ao seu
    <a href="{{ url('/painel-revendedor') }}">painel de revendedor</a>.
  </p>

  <div class="footer">
    <p>Equipa AngolaWiFi</p>
  </div>

</div>
</body>
</html>
