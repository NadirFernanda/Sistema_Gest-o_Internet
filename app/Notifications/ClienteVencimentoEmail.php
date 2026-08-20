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
        $dataTerminoStr = $this->formatDataTermino();
        $dataTermino    = $this->parseDateTermino();
        $hoje           = \Carbon\Carbon::today();

        if ($dataTermino === null) {
            $linhaInfo = "Informamos que a sua subscrição de internet encontra-se próxima da data de vencimento. Para garantir a continuidade do serviço sem interrupções, os pagamentos deverão ser efectuados exclusivamente através da nossa loja online.";
            $assunto   = 'Aviso de Vencimento – AngolaWiFi';
        } elseif ($dataTermino->isToday()) {
            $linhaInfo = "Informamos que a sua subscrição de internet *terminou hoje*, dia {$dataTerminoStr}. Para garantir a continuidade do serviço sem interrupções, os pagamentos deverão ser efectuados exclusivamente através da nossa loja online.";
            $assunto   = 'Subscrição Vencida Hoje – AngolaWiFi';
        } elseif ($dataTermino->lt($hoje)) {
            $diasAtraso = $hoje->diffInDays($dataTermino);
            $linhaInfo  = "Informamos que a sua subscrição de internet terminou no dia {$dataTerminoStr}" .
                ($diasAtraso > 0 ? " (há {$diasAtraso} dia(s))" : '') .
                " e ainda não identificámos o pagamento da renovação. Para garantir a continuidade do serviço, os pagamentos deverão ser efectuados exclusivamente através da nossa loja online.";
            $assunto   = 'Subscrição Vencida – AngolaWiFi';
        } else {
            $diasRestantes = $hoje->diffInDays($dataTermino);
            $linhaInfo  = "Informamos que a sua subscrição de internet termina dentro de {$diasRestantes} dia(s), no dia {$dataTerminoStr}. Para garantir a continuidade do serviço sem interrupções, os pagamentos deverão ser efectuados exclusivamente através da nossa loja online.";
            $assunto   = 'Aviso de Vencimento – AngolaWiFi';
        }

        return (new MailMessage)
            ->subject($assunto)
            ->greeting('Prezado(a) Cliente AngolaWiFi – ' . $notifiable->nome . ',')
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

    protected function parseDateTermino(): ?\Carbon\Carbon
    {
        try {
            if (!empty($this->plano->proxima_renovacao)) {
                return Carbon::parse($this->plano->proxima_renovacao)->startOfDay();
            }
            if (!empty($this->plano->data_ativacao) && $this->plano->ciclo) {
                $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$this->plano->ciclo));
                if ($cicloInt <= 0) { $cicloInt = (int)$this->plano->ciclo; }
                return Carbon::parse($this->plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
            }
        } catch (\Exception $e) {}
        return null;
    }

    protected function formatDataTermino(): string
    {
        $dt = $this->parseDateTermino();
        return $dt ? $dt->format('d/m/Y') : '-';
    }
}
