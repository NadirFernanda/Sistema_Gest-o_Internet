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
    private string $merchantIdentifier;
    private string $apiKey;
    private string $webhookUrl;

    public function __construct()
    {
        $this->clientId           = config('services.pay4all.client_id');
        $this->clientSecret       = config('services.pay4all.client_secret');
        $this->resource           = config('services.pay4all.resource');
        $this->paymentMethod      = config('services.pay4all.payment_method');
        $this->notificationToken  = config('services.pay4all.notification_token');
        $this->tokenUrl           = config('services.pay4all.token_url');
        $this->apiUrl             = rtrim(config('services.pay4all.api_url'), '/');
        $this->merchantIdentifier = config('services.pay4all.merchant_identifier', '');
        $this->apiKey             = config('services.pay4all.api_key', '');
        $this->webhookUrl         = config('services.pay4all.webhook_url', '');
    }

    /**
     * Obtém (ou recupera do cache) o token de acesso OAuth2.
     */
    public function getToken(): string
    {
        return Cache::remember('pay4all_access_token_loja', 3300, function () {
            $response = Http::asForm()->post($this->tokenUrl, [
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

            $token = $response->json('access_token');
            if (empty($token)) {
                Log::error('Pay4All[loja]: token vazio na resposta', ['body' => $response->body()]);
                throw new \RuntimeException('Resposta inválida do gateway de pagamento Pay4All.');
            }

            return $token;
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

        if ($this->webhookUrl) {
            $payload['notificationUrl'] = $this->webhookUrl;
        }

        if ($this->merchantIdentifier && $this->apiKey) {
            $payload['options'] = [
                'MerchantIdentifier' => $this->merchantIdentifier,
                'ApiKey'             => $this->apiKey,
            ];
        }

        $response = $this->doChargeRequest($token, $payload);

        // Token expirado: limpa cache e retenta uma vez com token novo
        if ($response->status() === 401) {
            Cache::forget('pay4all_access_token_loja');
            Log::warning('Pay4All[loja]: token expirado (401), a renovar e a retentar', [
                'transactionId' => $transactionId,
            ]);
            $token    = $this->getToken();
            $response = $this->doChargeRequest($token, $payload);
        }

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

    private function doChargeRequest(string $token, array $payload): \Illuminate\Http\Client\Response
    {
        $headers = [
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/vnd.appypay.asyncapi+json',
            'Accept-Language' => 'pt-BR',
        ];

        // Azure APIM gateway requer a chave de subscrição como header HTTP
        if ($this->apiKey) {
            $headers['Ocp-Apim-Subscription-Key'] = $this->apiKey;
        }

        return Http::withToken($token)
            ->withHeaders($headers)
            ->post("{$this->apiUrl}/charges", $payload);
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
     *
     * Estrutura do webhook GPO (conforme PDF Pay4All v2.7):
     *   operationStatus: 1=Sucesso, 3=Cancelado/Expirado, 4=Falhado/Recusado, 5=Erro
     */
    public function parseWebhookPayload(array $payload): array
    {
        $operationStatus = (int) ($payload['operationStatus'] ?? -1);

        $successful = $operationStatus === 1;
        $status = match ($operationStatus) {
            1       => 'approved',
            3       => 'cancelled',
            4       => 'rejected',
            5       => 'failed',
            default => 'unknown',
        };

        return [
            'transaction_id'   => $payload['ekwanzaTransactionId'] ?? ($payload['id'] ?? null),
            'merchant_tx_id'   => $payload['merchantTransactionId'] ?? null,
            'status'           => $status,
            'operation_status' => $operationStatus,
            'successful'       => $successful,
            'amount'           => $payload['operationData']['amount'] ?? ($payload['amount'] ?? null),
            'currency'         => 'AOA',
            'message'          => null,
            'code'             => null,
        ];
    }

    private function sanitizeDescription(string $desc): string
    {
        return substr(preg_replace('/[^a-zA-Z0-9 _\-]/', '', $desc), 0, 50);
    }
}
