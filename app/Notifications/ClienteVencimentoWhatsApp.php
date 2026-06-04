<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\WhatsAppService;
use Carbon\Carbon;

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
        // Calcular data de término de forma consistente com o comando de dispatch
        try {
            if (!empty($this->plano->proxima_renovacao)) {
                $dataTermino = Carbon::parse($this->plano->proxima_renovacao)->startOfDay();
            } elseif (!empty($this->plano->data_ativacao) && $this->plano->ciclo) {
                $cicloInt = intval(preg_replace('/[^0-9]/', '', (string)$this->plano->ciclo));
                if ($cicloInt <= 0) { $cicloInt = (int)$this->plano->ciclo; }
                $dataTermino = Carbon::parse($this->plano->data_ativacao)->addDays($cicloInt - 1)->startOfDay();
            } else {
                $dataTermino = null;
            }
        } catch (\Exception $e) {
            $dataTermino = null;
        }

        $dataTerminoStr = $dataTermino ? $dataTermino->format('d/m/Y') : '';

        $vencido = $this->diasRestantes <= 0;

        $linhaInfo = $vencido
            ? "Informamos que a sua subscrição de internet encontra-se vencida desde o dia *{$dataTerminoStr}*. "
            : "Informamos que a sua subscrição de internet encontra-se próxima da data de vencimento, prevista para o dia *{$dataTerminoStr}*. ";

        $mensagem = "*Prezado(a) Cliente AngolaWiFi – {$this->cliente->nome},*\n\n" .
            "Cordiais saudações.\n\n" .
            $linhaInfo .
            "Para garantir a continuidade do serviço sem interrupções, os pagamentos das subscrições mensais deverão ser efectuados exclusivamente através da nossa loja online.\n\n" .
            "Para o efeito, siga por gentileza os passos abaixo indicados:\n\n" .
            "1. Acesse o portal através do link: www.angolawifi.ao\n" .
            "2. Clique em *\"Pagar Agora\"* no plano correspondente à sua subscrição;\n" .
            "3. Insira o número de telefone autenticado no sistema e, em seguida, clique em *\"Verificar Número\"*;\n" .
            "4. Clique em *\"Pagar Agora\"*;\n" .
            "5. Insira o número associado ao *Multicaixa Express* e finalize a compra.\n\n" .
            "Em caso de dúvidas ou suporte adicional, a nossa equipa encontra-se à disposição através do contacto:\n" .
            "📞 (+244) 949 364 505\n\n" .
            "Atenciosamente,\n" .
            "*AngolaWiFi – Conectando você sempre!*";
        $service = new WhatsAppService();
        return $service->enviarMensagem($numero, $mensagem);
    }
}
