<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WhatsAppService
{
    protected string $driver;

    // Evolution API
    protected string $evolutionUrl;
    protected string $evolutionKey;
    protected string $evolutionInstance;

    // HTTP genérico (UltraMsg / Z-API — legacy)
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->driver            = config('services.whatsapp.driver', 'evolution');
        $this->evolutionUrl      = rtrim((string) config('services.evolution.url', ''), '/');
        $this->evolutionKey      = (string) config('services.evolution.key', '');
        $this->evolutionInstance = (string) config('services.evolution.instance', '');
        $this->apiUrl            = (string) config('services.whatsapp.api_url', '');
        $this->token             = (string) config('services.whatsapp.token', '');
    }

    /**
     * Envia mensagem de WhatsApp.
     * @param string $numero Número no formato internacional sem '+', ex: 244XXXXXXXXX
     * @param string $mensagem Texto da mensagem
     */
    public function enviarMensagem(string $numero, string $mensagem): array
    {
        $numero = $this->normalizarNumero($numero);

        return match ($this->driver) {
            'evolution' => $this->enviarEvolution($numero, $mensagem),
            'http'      => $this->enviarHttp($numero, $mensagem),
            default     => throw new InvalidArgumentException("Driver WhatsApp desconhecido: {$this->driver}"),
        };
    }

    private function enviarEvolution(string $numero, string $mensagem): array
    {
        if (! $this->evolutionUrl || ! $this->evolutionKey || ! $this->evolutionInstance) {
            throw new InvalidArgumentException(
                'Evolution API requer EVOLUTION_API_URL, EVOLUTION_API_KEY e EVOLUTION_API_INSTANCE'
            );
        }

        $endpoint = "{$this->evolutionUrl}/message/sendText/{$this->evolutionInstance}";

        $response = Http::withHeaders([
            'apikey'       => $this->evolutionKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'number' => $numero,
            'text'   => $mensagem,
        ]);

        if ($response->successful()) {
            Log::info('WhatsApp Evolution: mensagem enviada', ['to' => $numero]);
            return $response->json() ?? [];
        }

        $body = $response->body();
        Log::error('WhatsApp Evolution: falha no envio', [
            'status' => $response->status(),
            'body'   => substr($body, 0, 500),
            'to'     => $numero,
        ]);
        throw new \RuntimeException(
            'Evolution API falhou: ' . $response->status() . ' — ' . substr($body, 0, 300)
        );
    }

    private function enviarHttp(string $numero, string $mensagem): array
    {
        if (empty($this->apiUrl) || empty($this->token)) {
            throw new InvalidArgumentException(
                'Driver HTTP requer WHATSAPP_API_URL e WHATSAPP_API_TOKEN'
            );
        }

        $response = Http::withHeaders([
            'X-API-KEY'    => $this->token,
            'Content-Type' => 'application/json',
        ])->post(rtrim($this->apiUrl, '/') . '/messages/chat', [
            'to'      => $numero,
            'message' => $mensagem,
        ]);

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $body = $response->body();
        Log::error('WhatsApp HTTP: falha no envio', [
            'status' => $response->status(),
            'body'   => substr($body, 0, 500),
            'to'     => $numero,
        ]);
        throw new \RuntimeException(
            'WhatsApp HTTP falhou: ' . $response->status() . ' — ' . substr($body, 0, 300)
        );
    }

    // Remove '+', espaços e traços; garante formato 244XXXXXXXXX
    private function normalizarNumero(string $numero): string
    {
        return preg_replace('/\D/', '', $numero);
    }
}
