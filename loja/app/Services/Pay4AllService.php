<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Pay4AllService
{
    private string $clientId;
    private string $clientSecret;
    private string $resource;
    private string $paymentMethod;
    private string $notificationToken;
    private string $tokenUrl;
    private string $apiUrl;

    public function __construct()
    {
        $this->clientId          = config('services.pay4all.client_id');
        $this->clientSecret      = config('services.pay4all.client_secret');
        $this->resource          = config('services.pay4all.resource');
        $this->paymentMethod     = config('services.pay4all.payment_method');
        $this->notificationToken = config('services.pay4all.notification_token');
        $this->tokenUrl          = config('services.pay4all.token_url');
        $this->apiUrl            = rtrim(config('services.pay4all.api_url'), '/');
    }

    /**
     * Obtém (ou recupera do cache) o token de acesso OAuth2.
     */
    public function getToken(): string
    {
        return Cache::remember('pay4all_access_token_loja', 3300, function () {
            $response = Http::asForm()->get($this->tokenUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'resource'      => $this->resource,
            ]);

            if ($response->failed()) {
                Log::error('Pay4All[loja]: falha ao obter token', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Não foi possível autenticar com o gateway de pagamento Pay4All.');
            }

            return $response->json('access_token');
        });
    }

    /**
     * Cria uma cobrança GPO (Multicaixa Express) de forma assíncrona.
     */
    public function createCharge(
        float $amount,
        string $phoneNumber,
        string $transactionId,
        string $description = 'Compra AngolaWiFi',
        ?string $customerName = null
    ): array {
        $token = $this->getToken();

        $payload = [
            'amount'                => $amount,
            'currency'              => 'AOA',
            'description'          => $this->sanitizeDescription($description),
            'merchantTransactionId' => substr(preg_replace('/[^a-zA-Z0-9]/', '', $transactionId), 0, 15),
            'paymentMethod'        => $this->paymentMethod,
            'paymentInfo'          => [
                'phoneNumber' => $phoneNumber,
            ],
            'notify' => [
                'name'            => $customerName ?? 'Cliente',
                'telephone'       => $phoneNumber,
                'smsNotification' => true,
            ],
        ];

        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type'    => 'application/json',
                'Accept'          => 'application/vnd.appypay.asyncapi+json',
                'Accept-Language' => 'pt-BR',
            ])
            ->post("{$this->apiUrl}/charges", $payload);

        Log::info('Pay4All[loja]: createCharge', [
            'transactionId' => $transactionId,
            'phone'         => substr($phoneNumber, 0, 6) . '***',
            'amount'        => $amount,
            'status'        => $response->status(),
        ]);

        if ($response->failed() && $response->status() !== 202) {
            Log::error('Pay4All[loja]: erro ao criar cobrança', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Erro ao iniciar pagamento: ' . ($response->json('responseStatus.message') ?? 'Erro desconhecido'));
        }

        return $response->json() ?? [];
    }

    /**
     * Verifica se o token de notificação do webhook é válido.
     */
    public function verifyNotificationToken(string $token): bool
    {
        return hash_equals($this->notificationToken, $token);
    }

    /**
     * Processa o payload do webhook e retorna os dados normalizados.
     */
    public function parseWebhookPayload(array $payload): array
    {
        return [
            'transaction_id' => $payload['id'] ?? null,
            'merchant_tx_id' => $payload['merchantTransactionId'] ?? null,
            'status'         => strtolower($payload['responseStatus']['status'] ?? 'unknown'),
            'code'           => $payload['responseStatus']['code'] ?? null,
            'message'        => $payload['responseStatus']['message'] ?? null,
            'successful'     => (bool) ($payload['responseStatus']['successful'] ?? false),
            'amount'         => $payload['amount'] ?? null,
            'currency'       => $payload['currency'] ?? 'AOA',
        ];
    }

    private function sanitizeDescription(string $desc): string
    {
        return substr(preg_replace('/[^a-zA-Z0-9 _\-]/', '', $desc), 0, 50);
    }
}
