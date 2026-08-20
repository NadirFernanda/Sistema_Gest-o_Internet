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
    public string $estagio;

    /**
     * @param string $estagio Um de: '5d', '3d', '0d', 'followup' — controla qual das
     *                        4 mensagens do ciclo de vencimento é enviada.
     */
    public function __construct($cliente, $plano, $diasRestantes, string $estagio = '5d')
    {
        $this->cliente = $cliente;
        $this->plano = $plano;
        $this->diasRestantes = $diasRestantes;
        $this->estagio = $estagio;
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
        $hoje = \Carbon\Carbon::today();

        if ($dataTermino === null) {
            $linhaInfo = "A sua subscrição de internet encontra-se próxima da data de vencimento. ";
        } elseif ($dataTermino->isToday()) {
            $linhaInfo = "A sua subscrição de internet *terminou hoje*, dia *{$dataTerminoStr}*. ";
        } elseif ($dataTermino->lt($hoje)) {
            $diasAtraso = $hoje->diffInDays($dataTermino);
            $linhaInfo = "A sua subscrição de internet *terminou no dia {$dataTerminoStr}*" .
                ($diasAtraso > 0 ? " (há {$diasAtraso} dia(s))" : '') .
                " e ainda não identificámos o pagamento da renovação. ";
        } else {
            $diasRestantes = $hoje->diffInDays($dataTermino);
            $linhaInfo = "A sua subscrição de internet termina *dentro de {$diasRestantes} dia(s)*, no dia *{$dataTerminoStr}*. ";
        }

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
        return $service->enviarMensagem($numero, $mensagem, 'vencimento_' . $this->estagio, $this->cliente->nome ?? null);
    }
}
