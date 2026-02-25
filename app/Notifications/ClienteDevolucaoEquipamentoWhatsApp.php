<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\WhatsAppService;

class ClienteDevolucaoEquipamentoWhatsApp extends Notification implements ShouldQueue
{
    use Queueable;

    protected $cliente;

    public function __construct($cliente)
    {
        $this->cliente = $cliente;
    }

    public function via($notifiable)
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable)
    {
        $numero = $this->cliente->contato ?? ($notifiable->contato ?? null);
        $nome = $this->cliente->nome ?? ($notifiable->nome ?? 'Cliente');
        $mensagem = "Prezado(a) {$nome},\n\n" .
            "Cordiais saudações.\n\n" .
            "Verificamos que o seu plano de internet se encontra em situação de incumprimento há mais de 30 dias, não tendo sido possível a regularização do pagamento até à presente data.\n\n" .
            "Deste modo, informamos que será necessário proceder à devolução do equipamento instalado na sua residência, conforme os termos contratuais do serviço.\n\n" .
            "Solicitamos que entre em contacto connosco no prazo máximo de 5 dias para agendamento da recolha ou entrega do equipamento.\n\n" .
            "(+244) 949 364 505\n\n" .
            "Angola_WiFi – Conectando você sempre!";

        $service = new WhatsAppService();
        return $service->enviarMensagem($numero, $mensagem);
    }
}
