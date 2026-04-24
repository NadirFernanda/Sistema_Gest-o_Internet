<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Services\AutovendaOrderService;
use App\Services\Pay4AllService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pay4AllController — Fluxo Multicaixa Express (GPO) para a Loja AngolaWiFi.
 *
 * Fluxo:
 *   1. GET  /pagar/mcx/{order}          → formulário de introdução do número MCX
 *   2. POST /pagar/mcx/{order}          → valida telemóvel, chama Pay4All API, redireciona para aguardar
 *   3. GET  /pagar/mcx/{order}/aguardar → página de espera com polling automático
 *   4. GET  /pagar/mcx/{order}/status   → endpoint JSON para o polling
 *   5. POST /webhooks/pay4all           → callback assíncrono do gateway (CSRF-exempt)
 */
class Pay4AllController extends Controller
{
    public function __construct(
        private Pay4AllService $pay4all,
        private AutovendaOrderService $orderService,
    ) {}

    /**
     * Mostra o formulário de introdução do número Multicaixa Express.
     */
    public function iniciar(AutovendaOrder $order)
    {
        // Redireciona se a ordem já foi paga
        if ($order->isPaid()) {
            return redirect()->route('store.checkout.confirm', ['order' => $order->id]);
        }

        return view('store.pagamento-mcx-iniciar', compact('order'));
    }

    /**
     * Valida o número MCX, chama a API Pay4All e redireciona para a página de espera.
     */
    public function processar(Request $request, AutovendaOrder $order)
    {
        if ($order->isPaid()) {
            return redirect()->route('store.checkout.confirm', ['order' => $order->id]);
        }

        $validated = $request->validate([
            'telefone' => ['required', 'regex:/^(244)?9[0-9]{8}$/'],
        ], [
            'telefone.required' => 'Introduza o número de telemóvel Multicaixa Express.',
            'telefone.regex'    => 'Número inválido. Formato: 9XXXXXXXX ou 2449XXXXXXXX.',
        ]);

        // Normaliza para formato internacional com indicativo 244
        $phone = $validated['telefone'];
        if (!str_starts_with($phone, '244')) {
            $phone = '244' . $phone;
        }

        // Merchant transaction ID único (max 15 chars, alfanumérico)
        $merchantId = 'AW' . $order->id . strtoupper(Str::random(4));
        $merchantId = substr(preg_replace('/[^a-zA-Z0-9]/', '', $merchantId), 0, 15);

        // Guarda referência antes de chamar o gateway
        $order->update([
            'payment_reference' => $merchantId,
            'payment_gateway'   => 'pay4all',
            'customer_phone'    => $phone,
        ]);

        try {
            $this->pay4all->createCharge(
                amount:        (float) $order->amount_aoa,
                phoneNumber:   $phone,
                transactionId: $merchantId,
                description:   'AngolaWiFi ' . $order->plan_name,
                customerName:  $order->customer_name ?? 'Cliente',
            );
        } catch (\Throwable $e) {
            Log::error('Pay4All[loja]: erro ao iniciar cobrança', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withErrors([
                'gateway' => 'Não foi possível iniciar o pagamento. Tente novamente ou contacte o suporte.',
            ])->withInput();
        }

        return redirect()->route('pay4all.aguardar', $order);
    }

    /**
     * Página de espera — aguarda confirmação do pagamento pelo cliente na app MCX.
     */
    public function aguardar(AutovendaOrder $order)
    {
        return view('store.pagamento-mcx-aguardar', compact('order'));
    }

    /**
     * Endpoint JSON para polling do estado da ordem.
     */
    public function status(AutovendaOrder $order)
    {
        $order->refresh();

        $data = [
            'status'  => $order->status,
            'is_paid' => $order->isPaid(),
        ];

        if ($order->isPaid()) {
            $data['wifi_code']    = $order->wifi_code;
            $data['redirect_url'] = route('store.checkout.confirm', $order->id);
        }

        return response()->json($data);
    }

    /**
     * Webhook assíncrono do Pay4All (AppyPay).
     * CSRF-exempt — ver loja/bootstrap/app.php.
     */
    public function webhook(Request $request)
    {
        // Verificação do token de notificação
        $notificationToken = $request->header('X-Notification-Token')
            ?? $request->header('Authorization')
            ?? $request->query('token', '');

        $notificationToken = str_replace('Bearer ', '', $notificationToken);

        if (! $this->pay4all->verifyNotificationToken($notificationToken)) {
            Log::warning('Pay4All[loja]: webhook com token inválido', ['ip' => $request->ip()]);
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $payload = $this->pay4all->parseWebhookPayload($request->all());

        Log::info('Pay4All[loja]: webhook recebido', $payload);

        $merchantTxId = $payload['merchant_tx_id'];
        if (! $merchantTxId) {
            return response()->json(['error' => 'missing_merchant_tx_id'], 400);
        }

        $order = AutovendaOrder::where('payment_reference', $merchantTxId)->first();
        if (! $order) {
            return response()->json(['error' => 'order_not_found'], 404);
        }

        // Idempotência — se já foi processado, devolve sucesso silenciosamente
        if ($order->isPaid()) {
            return response()->json(['status' => 'already_processed']);
        }

        if ($payload['successful'] && in_array($payload['status'], ['approved', 'success', 'paid', 'completed'], true)) {
            try {
                $this->orderService->confirmPaymentAndDeliver($order, $payload['transaction_id'] ?? $merchantTxId);
            } catch (\Throwable $e) {
                Log::error('Pay4All[loja]: erro ao confirmar pagamento via webhook', [
                    'merchant_tx_id' => $merchantTxId,
                    'error'          => $e->getMessage(),
                ]);
                return response()->json(['error' => 'delivery_failed'], 500);
            }
        } elseif (in_array($payload['status'], ['rejected', 'failed', 'cancelled', 'expired', 'timeout'], true)) {
            $order->update(['status' => AutovendaOrder::STATUS_FAILED]);
        }

        return response()->json(['status' => 'processed']);
    }
}
