<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClienteVencimentoAlert extends Notification
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
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Aviso de Vencimento de Serviço')
            ->mailer('smtp')
            ->greeting('Prezado(a) ' . $this->cliente->nome . ',')
            ->line('Informamos que o seu serviço/plano "' . $this->plano->nome . '" irá vencer em ' . $this->diasRestantes . ' dia(s).')
            ->line('📅 Data de término: ' . ($this->plano->data_ativacao ? date('d/m/Y', strtotime($this->plano->data_ativacao . ' + ' . $this->plano->ciclo . ' days')) : ''))
            ->line('Solicitamos, por gentileza, que entre em contacto connosco para proceder à renovação ou para esclarecer qualquer dúvida.')
            ->salutation('Atenciosamente, Equipe LuandaWiFi');
    }
}
