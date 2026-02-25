<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ClienteDevolucaoEquipamentoEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $cliente;

    public function __construct($cliente)
    {
        $this->cliente = $cliente;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $nome = $this->cliente->nome ?? ($notifiable->nome ?? 'Cliente');
        return (new MailMessage)
            ->subject('Aviso de Devolução de Equipamento – Angola_WiFi')
            ->greeting('Prezado(a) ' . $nome . ',')
            ->line('Cordiais saudações.')
            ->line('Verificamos que o seu plano de internet se encontra em situação de incumprimento há mais de 30 dias, não tendo sido possível a regularização do pagamento até à presente data.')
            ->line('Deste modo, informamos que será necessário proceder à devolução do equipamento instalado na sua residência, conforme os termos contratuais do serviço.')
            ->line('Solicitamos, por gentileza, que entre em contacto connosco no prazo máximo de 5 dias, através do contacto abaixo, para agendamento da recolha ou entrega do equipamento.')
            ->line('(+244) 949 364 505')
            ->line('O não cumprimento poderá implicar encaminhamento do processo para vias administrativas ou legais.')
            ->line('Agradecemos a sua compreensão.')
            ->salutation('Angola_WiFi – Conectando você sempre!');
    }
}
