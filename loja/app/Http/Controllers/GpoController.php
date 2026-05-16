<?php

namespace App\Http\Controllers;

use App\Models\AutovendaOrder;
use App\Services\AutovendaOrderService;
use App\Services\GpoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * GpoController — Pagamento via webframe EMIS GPO (cartão + Multicaixa Express).
 *
 * Fluxo:
 *   1. GET  /pagar/gpo/{order}        → solicita token ao GPO, mostra iframe de pagamento
 *   2. POST /webhooks/gpo             → callback server-to-server do GPO (CSRF-exempt)
 *   3. GET  /pagar/gpo/{order}/status → polling JSON para a página de espera
 */
class GpoController extends Controller
{
    public function __construct(
        private GpoService $gpo,
        private AutovendaOrderService $orderService,
    ) {}

    /**
     * Solicita token de compra ao GPO e apresenta o iframe de pagamento.
     */
    public function show(AutovendaOrder $order)
    {
        if ($order->isPaid()) {
            return redirect()->route('store.checkout.confirm', $order->id);
        }

        // Gera referência única (max 15 chars alfanumérico)
        $reference = 'AW' . $order->id . strtoupper(Str::random(4));
        $reference = substr(preg_replace('/[^a-zA-Z0-9]/', '', $reference), 0, 15);

        $callbackUrl = route('webhooks.gpo');

        try {
            $iframeUrl = $this->gpo->createPurchaseToken($order, $reference, $callbackUrl);
        } catch (\Throwable $e) {
            Log::error('GPO: erro ao iniciar pagamento', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return back()->withErrors([
                'gateway' => 'Não foi possível iniciar o pagamento. Tente novamente ou contacte o suporte.',
            ]);
        }

        $order->update([
            'payment_reference' => $reference,
            'payment_gateway'   => 'gpo',
            'status'            => AutovendaOrder::STATUS_AWAITING_PAYMENT,
        ]);

        return view('store.pagamento-gpo', compact('order', 'iframeUrl'));
    }

    /**
     * Callback server-to-server enviado pelo GPO após conclusão do pagamento.
     * CSRF-exempt — ver bootstrap/app.php.
     */
    public function callback(Request $request)
    {
        $data = $request->all();

        Log::info('GPO: callback recebido', [
            'ip'   => $request->ip(),
            'body' => $data,
        ]);

        $parsed = $this->gpo->parseCallback($data);

        $merchantRef = $parsed['merchant_ref'];
        if (! $merchantRef) {
            Log::warning('GPO: callback sem merchantReference', ['body' => $data]);
            return response()->json(['error' => 'missing_reference'], 400);
        }

        $order = AutovendaOrder::where('payment_reference', $merchantRef)->first();
        if (! $order) {
            Log::warning('GPO: ordem não encontrada para referência', ['ref' => $merchantRef]);
            return response()->json(['error' => 'order_not_found'], 404);
        }

        // Idempotência — se já foi processado, ignora
        if ($order->isPaid()) {
            return response()->json(['status' => 'already_processed']);
        }

        if ($parsed['successful']) {
            try {
                $this->orderService->confirmPaymentAndDeliver(
                    $order,
                    $parsed['transaction_id'] ?? $merchantRef
                );
            } catch (\Throwable $e) {
                Log::error('GPO: erro ao confirmar pagamento', [
                    'ref'   => $merchantRef,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'delivery_failed'], 500);
            }
        } elseif (in_array($parsed['status'], ['rejected', 'cancelled', 'expired', 'reversed'], true)) {
            $order->update(['status' => AutovendaOrder::STATUS_FAILED]);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Endpoint JSON para polling do estado da ordem (usado pelo JavaScript da view).
     */
    public function status(AutovendaOrder $order)
    {
        $order->refresh();

        $statusPt = match (strtolower($order->status ?? '')) {
            'awaiting_payment', 'pending' => 'A aguardar pagamento',
            'paid', 'approved'            => 'Pago',
            'failed', 'rejected'          => 'Falhou',
            'cancelled'                   => 'Cancelado',
            'expired'                     => 'Expirado',
            default                       => ucfirst($order->status ?? 'Desconhecido'),
        };

        $data = [
            'status'    => $order->status,
            'status_pt' => $statusPt,
            'is_paid'   => $order->isPaid(),
        ];

        if ($order->isPaid()) {
            $data['wifi_code']    = $order->wifi_code;
            $data['redirect_url'] = route('store.checkout.confirm', $order->id);
        }

        return response()->json($data);
    }
}
