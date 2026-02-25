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

        $mensagem = "Prezado(a) {$this->cliente->nome},\n\n" .
            "Informamos que o seu serviço/plano \"{$this->plano->nome}\" irá vencer em {$this->diasRestantes} dia(s).\n\n" .
            "📅 Data de término: " . $dataTerminoStr . "\n\n" .
            "Para evitar a interrupção do serviço, recomendamos a regularização do pagamento através do link: www.luandawifi.ao\n\n" .
            "O pagamento também pode ser efetuado por transferência bancária:\n\n" .
            "IBAN: AO06.0060.0106.0100.2567.0410.4\n" .
            "Entidade: MR TEXA PRESTAÇÃO DE SERVIÇOS, LDA\n\n" .
            "Em caso de dúvida, estamos à disposição: (+244) 949 364 505\n\n" .
            "Atenciosamente,\nAngola_WiFi – Conectando você sempre!";
        $service = new WhatsAppService();
        return $service->enviarMensagem($numero, $mensagem);
    }
}
