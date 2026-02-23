<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ClienteVencimentoEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $plano;
    protected $diasRestantes;

    public function __construct($plano, $diasRestantes)
    {
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
            ->subject('Aviso de Vencimento de Plano')
            ->greeting('Prezado(a) ' . $notifiable->nome . ',')
            ->line('Informamos que o seu serviço/plano "' . $this->plano->nome . '" irá vencer em ' . $this->diasRestantes . ' dia(s).')
            ->line('📅 Data de término: ' . ($this->plano->data_ativacao ? date('d/m/Y', strtotime($this->plano->data_ativacao . ' +'.$this->plano->ciclo.' days')) : '-'))
            ->line('Solicitamos, por gentileza, que entre em contacto connosco para proceder à renovação ou para esclarecer qualquer dúvida.')
            ->salutation('Atenciosamente, Equipe LuandaWiFi');
    }
}
