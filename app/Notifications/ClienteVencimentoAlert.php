<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

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
            ->line('📅 Data de término: ' . $this->formatDataTermino())
            ->line('Solicitamos, por gentileza, que entre em contacto connosco para proceder à renovação ou para esclarecer qualquer dúvida.')
            ->salutation('Atenciosamente, Equipe LuandaWiFi');
    }

    /**
     * Calcula e formata a data de término de forma consistente com o comando de dispatch.
     * @return string
     */
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
                return '';
            }
            return $dt->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }
}
