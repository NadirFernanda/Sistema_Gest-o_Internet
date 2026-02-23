<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\WhatsAppService;

class ClienteVencimentoWhatsApp extends Notification
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
        $mensagem = "Prezado(a) {$this->cliente->nome},\n\n" .
            "Informamos que o seu serviço/plano \"{$this->plano->nome}\" irá vencer em {$this->diasRestantes} dia(s).\n\n" .
            "📅 Data de término: " .
            ($this->plano->data_ativacao ? date('d/m/Y', strtotime($this->plano->data_ativacao . ' + ' . $this->plano->ciclo . ' days')) : '') .
            "\n\nSolicitamos, por gentileza, que entre em contacto connosco para proceder à renovação ou para esclarecer qualquer dúvida.\n\nAtenciosamente,\nEquipe LuandaWiFi";
        $service = new WhatsAppService();
        return $service->enviarMensagem($numero, $mensagem);
    }
}
