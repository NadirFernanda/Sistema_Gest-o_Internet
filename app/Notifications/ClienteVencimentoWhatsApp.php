<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\WhatsAppService;

class ClienteVencimentoWhatsApp extends Notification implements ShouldQueue
{
    use Queueable;

    public $cliente;
    public $plano;
    public $diasRestantes;

    public function __construct($cliente, $plano, $diasRestantes)
    {
        $this->cliente = $cliente;
        $this->plano = $plano;
        $this->diasRestantes = $diasRestantes;
    }

    public function via($notifiable)
    {
        return ['whatsapp'];
    }

    public function toWhatsApp($notifiable)
    {
        $numero = $this->cliente->contato;
        $mensagem = "Olá, {$this->cliente->nome}! Seu serviço/plano '{$this->plano->nome}' irá vencer em {$this->diasRestantes} dia(s). Data de término: " .
            ($this->plano->data_ativacao ? date('d/m/Y', strtotime($this->plano->data_ativacao . ' + ' . $this->plano->ciclo . ' days')) : '') .
            ". Por favor, entre em contato para renovação ou dúvidas. Equipe LuandaWiFi.";
        $service = new WhatsAppService();
        return $service->enviarMensagem($numero, $mensagem);
    }
}
