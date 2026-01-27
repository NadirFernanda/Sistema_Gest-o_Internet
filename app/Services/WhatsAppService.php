<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        // Configure aqui sua URL e token da API (UltraMsg ou Z-API)
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->token = config('services.whatsapp.token');
    }

    /**
     * Envia mensagem de WhatsApp para um número.
     * @param string $numero Número no formato internacional, ex: 244XXXXXXXXX
     * @param string $mensagem Texto da mensagem
     * @return array|bool
     */
    public function enviarMensagem($numero, $mensagem)
    {
        // Exemplo UltraMsg
        $response = Http::withHeaders([
            'X-API-KEY' => $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl . '/messages/chat', [
            'to' => $numero,
            'message' => $mensagem,
        ]);

        if ($response->successful()) {
            return $response->json();
        }
        return false;
    }
}
