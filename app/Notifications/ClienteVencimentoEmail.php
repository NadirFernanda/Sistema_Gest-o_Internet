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
            ->greeting('Olá, ' . $notifiable->nome)
            ->line('Seu plano "' . $this->plano->nome . '" está prestes a vencer!')
            ->line('Faltam ' . $this->diasRestantes . ' dias para o término do serviço.')
            ->line('Data de término: ' . ($this->plano->data_ativacao ? date('d/m/Y', strtotime($this->plano->data_ativacao . ' +'.$this->plano->ciclo.' days')) : '-'))
            ->line('Por favor, entre em contato para renovação ou mais informações.')
            ->salutation('Atenciosamente, SGA - Mr. Texas');
    }
}
