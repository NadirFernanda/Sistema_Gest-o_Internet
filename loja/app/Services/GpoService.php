<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GpoService
{
    private string $frameToken;
    private string $frameTokenUrl;
    private string $webframeUrl;

    public function __construct()
    {
        $this->frameToken    = config('services.gpo.frame_token');
        $this->frameTokenUrl = rtrim(config('services.gpo.frame_token_url'), '/');
        $this->webframeUrl   = rtrim(config('services.gpo.webframe_url'), '/');
    }

    /**
     * Solicita ao GPO um token de compra (purchase token).
     * Devolve o URL completo do iframe a embutir na página de pagamento.
     */
    public function createPurchaseToken(
        float $amountAoa,
        string $reference,
        string $callbackUrl,
    ): string {
        return $this->createPurchaseTokenDetails($amountAoa, $reference, $callbackUrl)['url'];
    }

    /**
     * Creates a purchase token and returns the official WebFrame URL and TTL.
     */
    public function createPurchaseTokenDetails(
        float $amountAoa,
        string $reference,
        string $callbackUrl,
    ): array {
        $amount = number_format($amountAoa, 2, '.', '');

        $payload = [
            'reference'   => $reference,
            'amount'      => $amount,
            'token'       => $this->frameToken,
            'mobile'      => 'PAYMENT',
            'card'        => 'AUTHORIZATION',
            'callbackUrl' => $callbackUrl,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post($this->frameTokenUrl, $payload);

        if ($response->failed()) {
            Log::error('GPO: falha ao criar token de compra', [
                'reference' => $reference,
                'status'    => $response->status(),
            ]);
            throw new \RuntimeException('Não foi possível iniciar o pagamento GPO. Tente novamente.');
        }

        $tokenId = $response->json('id');
        if (empty($tokenId)) {
            $code    = $response->json('code') ?? $response->json('error');
            $message = $response->json('message') ?? '';
            Log::error('GPO: resposta sem token de compra', [
                'reference' => $reference,
                'code'      => $code,
                'message'   => $message,
            ]);
            throw new \RuntimeException('Resposta inválida do gateway GPO (código: ' . ($code ?? 'desconhecido') . ').');
        }

        Log::info('GPO: token de compra criado', [
            'reference'    => $reference,
            'time_to_live' => $response->json('timeToLive'),
        ]);

        return [
            'url' => $this->webframeUrl . '/frame?token=' . $tokenId,
            'time_to_live_ms' => is_numeric($response->json('timeToLive'))
                ? (int) $response->json('timeToLive')
                : null,
        ];
    }

    /**
     * Interpreta o payload do callback server-to-server enviado pelo GPO
     * após a conclusão (ou falha) da operação de pagamento.
     *
     * O GPO documenta o mesmo objecto JSON da "Consulta de uma Transação".
     * Apenas status ACCEPTED + transactionType PAYMENT representa um pagamento
     * confirmado para entrega; uma autorização não equivale a uma compra paga.
     */
    public function parseCallback(array $data): array
    {
        $stateValue = $data['status'] ?? $data['state'] ?? $data['transactionStatus'] ?? '';
        $typeValue = $data['transactionType'] ?? '';
        $state = is_string($stateValue) ? strtoupper($stateValue) : '';
        $transactionType = is_string($typeValue) ? strtoupper($typeValue) : '';
        $reference = $data['reference'] ?? null;
        if (is_array($reference)) {
            $reference = $reference['id'] ?? null;
        }

        $merchantReference = $data['merchantReferenceNumber']
            ?? $data['merchantReference']
            ?? (is_string($reference) ? $reference : null);
        if (! is_string($merchantReference) || $merchantReference === '') {
            $merchantReference = null;
        }
        $successful = $state === 'ACCEPTED' && $transactionType === 'PAYMENT';

        $status = match (true) {
            $successful                                                => 'approved',
            in_array($state, ['DECLINED', 'REJECTED'], true)           => 'rejected',
            in_array($state, ['CANCELLED', 'CANCELED'], true)          => 'cancelled',
            $state === 'REVERSED'                                       => 'reversed',
            $state === 'EXPIRED'                                        => 'expired',
            $state === 'AUTHORIZED' || $transactionType === 'AUTHORIZATION' => 'pending',
            default                                                    => 'unknown',
        };

        return [
            'transaction_id'   => $data['id'] ?? null,
            'merchant_ref'     => $merchantReference,
            'state'            => $state,
            'transaction_type' => $transactionType,
            'status'           => $status,
            'successful'       => $successful,
            'amount'           => $data['amount'] ?? null,
            'currency'         => $data['currency'] ?? null,
        ];
    }
}
