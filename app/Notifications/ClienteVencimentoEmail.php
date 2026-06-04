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
        $dataTermino = $this->formatDataTermino();
        $vencido     = $this->diasRestantes <= 0;

        $linhaInfo = $vencido
            ? "Informamos que a sua subscrição de internet encontra-se vencida desde o dia {$dataTermino}. Para garantir a continuidade do serviço sem interrupções, os pagamentos das subscrições mensais deverão ser efectuados exclusivamente através da nossa loja online."
            : "Informamos que a sua subscrição de internet encontra-se próxima da data de vencimento, prevista para o dia {$dataTermino}. Para garantir a continuidade do serviço sem interrupções, os pagamentos das subscrições mensais deverão ser efectuados exclusivamente através da nossa loja online.";

        $assunto = $vencido
            ? 'Subscrição Vencida – AngolaWiFi'
            : 'Aviso de Vencimento – AngolaWiFi';

        return (new MailMessage)
            ->subject($assunto)
            ->greeting('Prezado(a) ' . $notifiable->nome . ',')
            ->line('Cordiais saudações.')
            ->line($linhaInfo)
            ->line('Para o efeito, siga por gentileza os passos abaixo indicados:')
            ->line('1. Acesse o portal através do link: www.angolawifi.ao')
            ->line('2. Clique em "Pagar Agora" no plano correspondente à sua subscrição;')
            ->line('3. Insira o número de telefone autenticado no sistema e, em seguida, clique em "Verificar Número";')
            ->line('4. Clique em "Pagar Agora";')
            ->line('5. Insira o número associado ao Multicaixa Express e finalize a compra.')
            ->line('Em caso de dúvidas ou suporte adicional, a nossa equipa encontra-se à disposição através do contacto: (+244) 949 364 505')
            ->salutation('Atenciosamente, AngolaWiFi – Conectando você sempre!');
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
