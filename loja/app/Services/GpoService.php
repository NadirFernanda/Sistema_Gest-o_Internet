<?php

namespace App\Services;

use App\Models\AutovendaOrder;
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
     * Solicita ao GPO um token de compra (purchase token) para a ordem indicada.
     * Devolve o URL completo do iframe a embutir na página de pagamento.
     */
    public function createPurchaseToken(
        AutovendaOrder $order,
        string $reference,
        string $callbackUrl,
    ): string {
        $amount = number_format((float) $order->amount_aoa, 2, '.', '');

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
                'order_id' => $order->id,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new \RuntimeException('Não foi possível iniciar o pagamento GPO. Tente novamente.');
        }

        $tokenId = $response->json('id');
        if (empty($tokenId)) {
            $code    = $response->json('code') ?? $response->json('error');
            $message = $response->json('message') ?? '';
            Log::error('GPO: resposta sem token de compra', [
                'order_id' => $order->id,
                'code'     => $code,
                'message'  => $message,
                'body'     => $response->body(),
            ]);
            throw new \RuntimeException('Resposta inválida do gateway GPO (código: ' . ($code ?? 'desconhecido') . ').');
        }

        Log::info('GPO: token de compra criado', [
            'order_id'   => $order->id,
            'reference'  => $reference,
            'token_id'   => $tokenId,
            'time_to_live' => $response->json('timeToLive'),
        ]);

        return $this->webframeUrl . '/frame?token=' . $tokenId;
    }

    /**
     * Interpreta o payload do callback server-to-server enviado pelo GPO
     * após a conclusão (ou falha) da operação de pagamento.
     *
     * O GPO envia o mesmo objeto JSON que devolve em "Consulta de uma Transação".
     * Campos relevantes observados: id, state, merchantReference, amount.
     *
     * Estados conhecidos do GPO:
     *   AUTHORIZED → autorizado (aguarda captura)
     *   PURCHASED  → compra confirmada (sucesso)
     *   DECLINED   → recusado
     *   CANCELLED  → cancelado
     *   REVERSED   → revertido
     *   EXPIRED    → expirado
     */
    public function parseCallback(array $data): array
    {
        $state = strtoupper($data['state'] ?? $data['transactionStatus'] ?? $data['status'] ?? '');

        $successful = in_array($state, ['PURCHASED', 'AUTHORIZED', 'APPROVED', 'SUCCESS', 'PAID'], true);

        $status = match (true) {
            in_array($state, ['PURCHASED', 'AUTHORIZED', 'APPROVED', 'SUCCESS', 'PAID'], true) => 'approved',
            in_array($state, ['DECLINED', 'REJECTED'], true)                                   => 'rejected',
            in_array($state, ['CANCELLED'], true)                                              => 'cancelled',
            in_array($state, ['REVERSED'], true)                                               => 'reversed',
            in_array($state, ['EXPIRED'], true)                                                => 'expired',
            default                                                                            => 'unknown',
        };

        return [
            'transaction_id'   => $data['id'] ?? null,
            'merchant_ref'     => $data['merchantReference'] ?? $data['reference'] ?? null,
            'state'            => $state,
            'status'           => $status,
            'successful'       => $successful,
            'amount'           => $data['amount'] ?? null,
        ];
    }
}
