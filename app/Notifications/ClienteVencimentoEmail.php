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
            ->subject('Aviso de Vencimento de Plano')
            ->greeting('Prezado(a) ' . $notifiable->nome . ',')
            ->line('Informamos que o seu serviço/plano "' . $this->plano->nome . '" irá vencer em ' . $this->diasRestantes . ' dia(s).')
            ->line('📅 Data de término: ' . $this->formatDataTermino())
            ->line('Solicitamos, por gentileza, que entre em contacto connosco para proceder à renovação ou para esclarecer qualquer dúvida.')
            ->salutation('Atenciosamente, Equipe LuandaWiFi');
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
