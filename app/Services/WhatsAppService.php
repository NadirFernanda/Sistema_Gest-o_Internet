<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WhatsAppService
{
    protected $apiUrl;
    protected $token;
    protected $driver;
    protected $twilioSid;
    protected $twilioToken;
    protected $twilioFrom;

    public function __construct()
    {
        // Driver pode ser 'http' (UltraMsg/Z-API) ou 'twilio'
        $this->driver = config('services.whatsapp.driver', 'http');
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->token = config('services.whatsapp.token');
        $this->twilioSid = config('services.twilio.sid');
        $this->twilioToken = config('services.twilio.token');
        $this->twilioFrom = config('services.twilio.from');
    }

    /**
     * Envia mensagem de WhatsApp para um número.
     * @param string $numero Número no formato internacional, ex: 244XXXXXXXXX
     * @param string $mensagem Texto da mensagem
     * @return array|bool
     */
    public function enviarMensagem($numero, $mensagem)
    {
        // Suporta driver Twilio (recomendado para POC) ou integrações HTTP (UltraMsg/Z-API)
        if ($this->driver === 'twilio') {
            if (!$this->twilioSid || !$this->twilioToken || !$this->twilioFrom) {
                throw new InvalidArgumentException('Twilio credentials missing (TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, TWILIO_WHATSAPP_FROM)');
            }
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->twilioSid}/Messages.json";
            $response = Http::withBasicAuth($this->twilioSid, $this->twilioToken)
                ->asForm()
                ->post($url, [
                    'From' => 'whatsapp:' . $this->twilioFrom,
                    'To' => 'whatsapp:' . $numero,
                    'Body' => $mensagem,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            $body = $response->body();
            Log::error('WhatsAppService Twilio send failed', ['status' => $response->status(), 'body' => $body, 'to' => $numero]);
            throw new \RuntimeException('Twilio WhatsApp send failed: ' . $response->status() . ' ' . substr($body,0,1000));
        }

        // Exemplo UltraMsg / HTTP padrão
        if (empty($this->apiUrl) || empty($this->token)) {
            throw new InvalidArgumentException('WhatsApp HTTP driver requires WHATSAPP_API_URL and WHATSAPP_API_TOKEN');
        }

        $response = Http::withHeaders([
            'X-API-KEY' => $this->token,
            'Content-Type' => 'application/json',
        ])->post(rtrim($this->apiUrl, '/') . '/messages/chat', [
            'to' => $numero,
            'message' => $mensagem,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        $body = $response->body();
        Log::error('WhatsAppService HTTP send failed', ['status' => $response->status(), 'body' => $body, 'to' => $numero]);
        throw new \RuntimeException('WhatsApp HTTP send failed: ' . $response->status() . ' ' . substr($body,0,1000));
    }
}
