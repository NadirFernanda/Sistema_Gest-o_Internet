<?php

namespace App\Notifications;

use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ComprovantePagamentoWhatsApp extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public $cobranca,
        public $cliente,
        public $plano
    ) {}

    public function via($notifiable): array
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable): mixed
    {
        $numero    = $this->cliente->contato;
        $valor     = number_format($this->cobranca->valor, 2, ',', '.');
        $descricao = $this->cobranca->descricao ?? 'subscrição';
        $novaData  = $this->plano->proxima_renovacao
            ? Carbon::parse($this->plano->proxima_renovacao)->format('d/m/Y')
            : '—';

        $mensagem =
            "✅ *Pagamento Confirmado – AngolaWiFi*\n\n" .
            "Prezado(a) *{$this->cliente->nome}*,\n\n" .
            "O seu pagamento foi processado com sucesso.\n\n" .
            "📋 *Detalhes:*\n" .
            "• Referência: #{$this->cobranca->id}\n" .
            "• Serviço: {$descricao}\n" .
            "• Valor: *{$valor} Kz*\n" .
            "• Próxima renovação: *{$novaData}*\n\n" .
            "Obrigado pela sua confiança.\n" .
            "*AngolaWiFi – Conectando você sempre!*";

        return (new WhatsAppService())->enviarMensagem($numero, $mensagem);
    }
}
