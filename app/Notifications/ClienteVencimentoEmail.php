<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

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
            ->subject('Aviso de Vencimento – Angola_WiFi')
            ->greeting('Prezado(a) ' . $notifiable->nome . ',')
            ->line('Cordiais saudações.')
            ->line('Informamos que o seu plano de internet vence no dia ' . $this->formatDataTermino() . '. Para evitar a interrupção do serviço, recomendamos a regularização atempada do pagamento através do link: www.luandawifi.ao')
            ->line('O pagamento também pode ser efetuado por transferência bancária, através das seguintes coordenadas:')
            ->line('IBAN: AO06.0060.0106.0100.2567.0410.4')
            ->line('Entidade: MR TEXA PRESTAÇÃO DE SERVIÇOS, LDA')
            ->line('Em caso de dúvida, estamos à disposição: (+244) 949 364 505')
            ->salutation('Atenciosamente, Angola_WiFi – Conectando você sempre!');
    }

    protected function formatDataTermino()
    {
        try {
            if (!empty($this->plano->proxima_renovacao)) {
                $dt = Carbon::parse($this->plano->proxima_renovacao)->startOfDay();
            } elseif (!empty($this->plano->data_ativacao) && $this->plano->ciclo) {
                $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$this->plano->ciclo));
                if ($cicloInt <= 0) { $cicloInt = (int)$this->plano->ciclo; }
                $dt = Carbon::parse($this->plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
            } else {
                return '-';
            }
            return $dt->format('d/m/Y');
        } catch (\Exception $e) {
            return '-';
        }
    }
}
