<?php

namespace App\Services;

use App\Exceptions\SaldoWhatsAppInsuficienteException;
use App\Models\WhatsappLedger;
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
     * @param string $tipo Categoria da mensagem para o extrato (ex.: 'vencimento', 'devolucao', 'comprovante', 'teste')
     *
     * @throws SaldoWhatsAppInsuficienteException Sem saldo — a mensagem nem chega a ser enviada.
     */
    public function enviarMensagem(string $numero, string $mensagem, string $tipo = 'outro'): array
    {
        $numero = $this->normalizarNumero($numero);

        // Pré-pago: sem saldo, nem tenta enviar. O débito só acontece depois
        // de o envio ser confirmado com sucesso — uma falha da API não cobra.
        if (! WhatsappLedger::debitarMensagem($numero, $tipo)) {
            Log::warning('WhatsApp: envio bloqueado por saldo insuficiente', ['to' => $numero, 'tipo' => $tipo]);
            throw new SaldoWhatsAppInsuficienteException(
                'Saldo insuficiente para enviar mensagem de WhatsApp. Carregue saldo antes de continuar.'
            );
        }

        try {
            return match ($this->driver) {
                'evolution' => $this->enviarEvolution($numero, $mensagem),
                'http'      => $this->enviarHttp($numero, $mensagem),
                default     => throw new InvalidArgumentException("Driver WhatsApp desconhecido: {$this->driver}"),
            };
        } catch (\Throwable $e) {
            // O envio falhou depois de já termos debitado — estornar o custo,
            // já que só se cobra por mensagens efectivamente entregues ao Evolution API.
            WhatsappLedger::carregar(
                WhatsappLedger::CUSTO_POR_MENSAGEM,
                'Estorno — falha no envio para ' . $numero . ' (' . $tipo . ')',
                null,
                substr($e->getMessage(), 0, 2000)
            );
            throw $e;
        }
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
        $n = preg_replace('/\D/', '', $numero);
        // Adiciona indicativo de Angola se o número tiver 9 dígitos (formato local)
        if (strlen($n) === 9 && str_starts_with($n, '9')) {
            $n = '244' . $n;
        }
        return $n;
    }
}
