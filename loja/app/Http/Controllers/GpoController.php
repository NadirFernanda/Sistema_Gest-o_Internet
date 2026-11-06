<?php

namespace App\Http\Controllers;

use App\Mail\ResellerMaintenancePaidMail;
use App\Models\AutovendaOrder;
use App\Models\ResellerApplication;
use App\Models\ResellerPurchase;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use App\Services\AutovendaOrderService;
use App\Services\GpoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
            return redirect()->away(URL::signedRoute('store.checkout.confirm', ['order' => $order->id]));
        }

        $meta = $order->meta ?? [];
        $attempt = $meta['gpo_attempt'] ?? [];
        $attemptExpiry = is_string($attempt['expires_at'] ?? null)
            ? Carbon::parse($attempt['expires_at'])
            : null;
        $terminalStatuses = [
            AutovendaOrder::STATUS_FAILED,
            AutovendaOrder::STATUS_CANCELLED,
            AutovendaOrder::STATUS_EXPIRED,
        ];
        $canReuseToken = ($attempt['reference'] ?? null) === $order->payment_reference
            && is_string($attempt['url'] ?? null)
            && $attemptExpiry?->isFuture()
            && ! in_array($order->status, $terminalStatuses, true);

        // Reuse the same EMIS token during refresh while it is valid. Once it
        // expires, create a fresh unique merchant reference and retain old ones
        // so a delayed callback can still be associated with this order.
        $needsNewReference = empty($order->payment_reference)
            || ! $canReuseToken
            || in_array($order->status, $terminalStatuses, true);

        if ($needsNewReference) {
            $reference = 'AW' . $order->id . strtoupper(Str::random(4));
            $reference = substr(preg_replace('/[^a-zA-Z0-9]/', '', $reference), 0, 15);
        } else {
            $reference = $order->payment_reference;
        }

        $iframeUrl = $canReuseToken ? $attempt['url'] : null;
        if (! $canReuseToken) {
            $callbackUrl = route('webhooks.gpo');

            try {
                $token = $this->gpo->createPurchaseTokenDetails(
                    (float) $order->amount_aoa,
                    $reference,
                    $callbackUrl
                );
                $iframeUrl = $token['url'];
                $previousReferences = $meta['gpo_references'] ?? [];
                if ($order->payment_reference && $order->payment_reference !== $reference) {
                    $previousReferences[] = $order->payment_reference;
                }

                $meta['gpo_references'] = array_values(array_unique($previousReferences));
                $meta['gpo_attempt'] = [
                    'reference'  => $reference,
                    'url'        => $iframeUrl,
                    'expires_at' => $token['time_to_live_ms']
                        ? now()->addMilliseconds($token['time_to_live_ms'])->toIso8601String()
                        : null,
                ];
            } catch (\Throwable $e) {
                Log::error('GPO: erro ao iniciar pagamento', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
                return back()->withErrors([
                    'gateway' => 'Não foi possível iniciar o pagamento. Tente novamente ou contacte o suporte.',
                ]);
            }
        }

        $paymentStarted = DB::transaction(function () use ($order, $reference, $meta) {
            $lockedOrder = AutovendaOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->isPaid()) {
                return false;
            }

            $storedMeta = $lockedOrder->meta ?? [];
            $references = array_merge(
                $storedMeta['gpo_references'] ?? [],
                $meta['gpo_references'] ?? []
            );
            if ($lockedOrder->payment_reference && $lockedOrder->payment_reference !== $reference) {
                $references[] = $lockedOrder->payment_reference;
            }
            $storedMeta['gpo_references'] = array_values(array_unique($references));
            $storedMeta['gpo_attempt'] = $meta['gpo_attempt'] ?? $storedMeta['gpo_attempt'] ?? [];

            $lockedOrder->update([
                'payment_reference' => $reference,
                'payment_gateway'   => 'gpo',
                'status'            => AutovendaOrder::STATUS_AWAITING_PAYMENT,
                'meta'              => $storedMeta,
            ]);

            return true;
        });

        if (! $paymentStarted) {
            return redirect()->away(URL::signedRoute('store.checkout.confirm', ['order' => $order->id]));
        }

        $order->refresh();

        // Regista o ID da ordem na sessão para que apenas esta sessão possa
        // fazer polling do estado e receber o código WiFi (protege contra IDOR).
        $allowed = array_unique(array_merge(session('gpo_order_ids', []), [$order->id]));
        session(['gpo_order_ids' => $allowed]);

        return view('store.pagamento-gpo', compact('order', 'iframeUrl'));
    }

    /**
     * Callback server-to-server enviado pelo GPO após conclusão do pagamento.
     * CSRF-exempt — ver bootstrap/app.php.
     */
    public function callback(Request $request)
    {
        $this->verifyCallbackSource($request, 'GPO');

        $data = $request->all();

        Log::info('GPO: callback recebido', [
            'ip'              => $request->ip(),
            'merchant_ref'    => $data['merchantReferenceNumber'] ?? $data['merchantReference'] ?? null,
            'transaction_id'  => $data['id'] ?? null,
            'transaction_type'=> $data['transactionType'] ?? null,
            'state'           => $data['status'] ?? $data['state'] ?? null,
        ]);

        $parsed = $this->gpo->parseCallback($data);

        $merchantRef = $parsed['merchant_ref'];
        if (! $merchantRef) {
            Log::warning('GPO: callback sem merchantReference', ['fields' => array_keys($data)]);
            return response()->json(['error' => 'missing_reference'], 400);
        }

        $order = AutovendaOrder::where('payment_reference', $merchantRef)
            ->orWhereJsonContains('meta->gpo_references', $merchantRef)
            ->first();
        if (! $order) {
            Log::warning('GPO: ordem não encontrada para referência', ['ref' => $merchantRef]);
            return response()->json(['error' => 'order_not_found'], 404);
        }

        // Idempotência — se já foi processado, ignora
        if ($order->isPaid()) {
            return response()->json(['status' => 'already_processed']);
        }

        // Verificar montante para mitigar ataques de callback forjado.
        // Desvia-se de callbacks que declarem um montante diferente do valor real da ordem.
        $callbackAmount = (float) ($parsed['amount'] ?? 0);
        if ($callbackAmount > 0 && abs($callbackAmount - (float) $order->amount_aoa) > 0.01) {
            Log::error('GPO: discrepância de montante — possível callback forjado', [
                'ref'      => $merchantRef,
                'expected' => (float) $order->amount_aoa,
                'received' => $callbackAmount,
            ]);
            return response()->json(['error' => 'amount_mismatch'], 400);
        }

        if ($parsed['successful']) {
            if (! is_numeric($parsed['amount']) || (float) $parsed['amount'] <= 0
                || strtoupper((string) $parsed['currency']) !== 'AOA'
            ) {
                Log::error('GPO: callback de pagamento sem montante ou moeda válidos', [
                    'ref'      => $merchantRef,
                    'currency' => $parsed['currency'],
                ]);
                return response()->json(['error' => 'missing_payment_details'], 400);
            }

            try {
                $this->orderService->confirmPaymentAndDeliver($order);
            } catch (\Throwable $e) {
                Log::error('GPO: erro ao confirmar pagamento', [
                    'ref'   => $merchantRef,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'delivery_failed'], 500);
            }
        } elseif ($order->payment_reference === $merchantRef
            && in_array($parsed['status'], ['rejected', 'cancelled', 'expired', 'reversed'], true)
        ) {
            $order->update(['status' => AutovendaOrder::STATUS_FAILED]);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Callback server-to-server do GPO para compras de revendedores.
     * CSRF-exempt — ver bootstrap/app.php.
     */
    public function resellerCallback(Request $request)
    {
        $this->verifyCallbackSource($request, 'GPO Revendedor');

        $data = $request->all();

        Log::info('GPO Revendedor: callback recebido', [
            'ip'               => $request->ip(),
            'merchant_ref'     => $data['merchantReferenceNumber'] ?? $data['merchantReference'] ?? null,
            'transaction_id'   => $data['id'] ?? null,
            'transaction_type' => $data['transactionType'] ?? null,
            'state'            => $data['status'] ?? $data['state'] ?? null,
        ]);

        $parsed = $this->gpo->parseCallback($data);

        $merchantRef = $parsed['merchant_ref'];
        if (! $merchantRef) {
            Log::warning('GPO Revendedor: callback sem merchantReference', ['fields' => array_keys($data)]);
            return response()->json(['error' => 'missing_reference'], 400);
        }

        // Verificar se é uma referência de revendedor (RV...)
        $purchases = ResellerPurchase::where('payment_reference', $merchantRef)
            ->where('status', 'pending')
            ->get();

        if ($purchases->isEmpty()) {
            Log::warning('GPO Revendedor: compras não encontradas', ['ref' => $merchantRef]);
            return response()->json(['error' => 'purchases_not_found'], 404);
        }

        // Idempotência — se já processado, ignorar
        if ($purchases->every(fn ($p) => $p->status === 'completed')) {
            return response()->json(['status' => 'already_processed']);
        }

        if ($parsed['successful']) {
            $expectedAmount = (float) $purchases->sum('net_amount_aoa');
            if (! is_numeric($parsed['amount']) || (float) $parsed['amount'] <= 0
                || strtoupper((string) $parsed['currency']) !== 'AOA'
                || abs((float) $parsed['amount'] - $expectedAmount) > 0.01
            ) {
                Log::error('GPO Revendedor: montante/moeda do callback não correspondem às compras', [
                    'reference' => $merchantRef,
                    'expected'  => $expectedAmount,
                    'received'  => $parsed['amount'],
                    'currency'  => $parsed['currency'],
                ]);
                return response()->json(['error' => 'payment_details_mismatch'], 400);
            }

            $planSlugs = $purchases->pluck('plan_slug')->toArray();
            $planMap   = VoucherPlan::whereIn('slug', $planSlugs)->get()->keyBy('slug');

            try {
                DB::transaction(function () use ($purchases, $planMap, $merchantRef) {
                    foreach ($purchases as $purchase) {
                        // Atribuir códigos disponíveis agora que o pagamento foi confirmado
                        $codes = WifiCode::where('plan_id', $purchase->plan_slug)
                            ->where('status', WifiCode::STATUS_AVAILABLE)
                            ->lockForUpdate()
                            ->limit($purchase->quantity)
                            ->get();

                        if ($codes->count() < $purchase->quantity) {
                            Log::error('GPO Revendedor: stock insuficiente no momento do pagamento', [
                                'purchase_id' => $purchase->id,
                                'plan'        => $purchase->plan_slug,
                                'needed'      => $purchase->quantity,
                                'available'   => $codes->count(),
                            ]);
                            // Entregar o que há e registar a diferença para o admin resolver
                            if ($codes->isEmpty()) continue;
                        }

                        $validityLabel = optional($planMap->get($purchase->plan_slug))->validity_label
                            ?? $purchase->plan_slug;

                        $codeLines = ['plano,codigo,validade'];
                        foreach ($codes as $wc) {
                            $codeLines[] = "{$purchase->plan_name},{$wc->code},{$validityLabel}";
                        }
                        Storage::disk('local')->put($purchase->csv_path, implode("\n", $codeLines) . "\n");

                        WifiCode::whereIn('id', $codes->pluck('id'))->update([
                            'status'               => WifiCode::STATUS_USED,
                            'used_at'              => now(),
                            'reseller_purchase_id' => $purchase->id,
                        ]);

                        $purchase->update([
                            'status'       => 'completed',
                            'codes_count'  => $codes->count(),
                            'paid_at'      => now(),
                        ]);
                    }
                });
            } catch (\Throwable $e) {
                Log::error('GPO Revendedor: erro ao completar compras', [
                    'reference' => $merchantRef,
                    'error'     => $e->getMessage(),
                ]);
                return response()->json(['error' => 'completion_failed'], 500);
            }
        } elseif (in_array($parsed['status'], ['rejected', 'cancelled', 'expired', 'reversed'], true)) {
            // Sem códigos reservados — apenas cancelar o registo de compra
            ResellerPurchase::where('payment_reference', $merchantRef)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        }

        return response()->json(['status' => 'processed']);
    }

    /**
     * Callback server-to-server do GPO para taxa de manutenção de revendedores.
     * Referência: MN{applicationId}Y{year}M{month} — parse direto, sem tabela extra.
     */
    public function maintenanceCallback(Request $request)
    {
        $this->verifyCallbackSource($request, 'GPO Manutenção');

        $data = $request->all();

        Log::info('GPO Manutenção: callback recebido', [
            'ip'               => $request->ip(),
            'merchant_ref'     => $data['merchantReferenceNumber'] ?? $data['merchantReference'] ?? null,
            'transaction_id'   => $data['id'] ?? null,
            'transaction_type' => $data['transactionType'] ?? null,
            'state'            => $data['status'] ?? $data['state'] ?? null,
        ]);

        $parsed = $this->gpo->parseCallback($data);

        $merchantRef = $parsed['merchant_ref'];
        if (! $merchantRef) {
            return response()->json(['error' => 'missing_reference'], 400);
        }

        // Parse referência: MN{id}Y{year}M{month}
        if (! preg_match('/^MN(\d+)Y(\d+)M(\d+)$/', $merchantRef, $m)) {
            Log::warning('GPO Manutenção: referência inválida', ['ref' => $merchantRef]);
            return response()->json(['error' => 'invalid_reference'], 400);
        }

        $applicationId = (int) $m[1];
        $year          = (int) $m[2];
        $month         = (int) $m[3];

        $application = ResellerApplication::find($applicationId);
        if (! $application) {
            Log::warning('GPO Manutenção: revendedor não encontrado', ['id' => $applicationId]);
            return response()->json(['error' => 'reseller_not_found'], 404);
        }

        // Idempotência
        if ((int) ($application->maintenance_paid_year ?? 0)  === $year
            && (int) ($application->maintenance_paid_month ?? 0) === $month) {
            return response()->json(['status' => 'already_processed']);
        }

        if ($parsed['successful']) {
            $expectedAmount = $application->maintenanceFeeAoa();
            if (! is_numeric($parsed['amount']) || (float) $parsed['amount'] <= 0
                || strtoupper((string) $parsed['currency']) !== 'AOA'
                || abs((float) $parsed['amount'] - $expectedAmount) > 0.01
            ) {
                Log::error('GPO Manutenção: montante/moeda do callback não correspondem à taxa', [
                    'reference' => $merchantRef,
                    'expected'  => $expectedAmount,
                    'received'  => $parsed['amount'],
                    'currency'  => $parsed['currency'],
                ]);
                return response()->json(['error' => 'payment_details_mismatch'], 400);
            }

            $application->update([
                'maintenance_paid_year'  => $year,
                'maintenance_paid_month' => $month,
                'maintenance_status'     => ResellerApplication::MAINTENANCE_OK,
            ]);

            // Alocar vouchers bónus e guardar notificação no meta do revendedor
            $allocated = $application->allocateMaintenanceVouchers($year, $month);
            if (! empty($allocated)) {
                $meta = $application->meta ?? [];
                $meta['bonus_notification'] = [
                    'year'      => $year,
                    'month'     => $month,
                    'allocated' => $allocated,
                ];
                $application->update(['meta' => $meta]);
            }

            // Enviar email de confirmação ao revendedor
            if ($application->email) {
                $bonusAoa = (int) ($application->bonus_vouchers_aoa ?? 0);
                try {
                    Mail::to($application->email)
                        ->send(new ResellerMaintenancePaidMail($application, $year, $month, $bonusAoa));
                } catch (\Throwable $e) {
                    Log::warning('GPO Manutenção: erro ao enviar email de confirmação', [
                        'reseller_id' => $applicationId,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            Log::info('GPO Manutenção: paga com sucesso', [
                'reseller_id' => $applicationId,
                'period'      => "$year/$month",
                'bonus'       => $allocated,
            ]);
        }

        return response()->json(['status' => 'processed']);
    }


    /**
     * Verifica a origem de callbacks GPO.
     *
     * IP allowlist. Em produção, configurar apenas os IPs confirmados com a EMIS.
     *
     * O manual EMIS disponível não documenta assinatura HMAC para este callback.
     */
    private function verifyCallbackSource(Request $request, string $context): void
    {
        $allowed = array_filter(array_map('trim', explode(',', config('services.gpo.callback_ips', ''))));

        if (empty($allowed)) {
            Log::critical("{$context}: callback GPO sem allowlist de IP configurada", ['ip' => $request->ip()]);
            if (app()->environment('production')) {
                abort(503, 'Callback GPO temporariamente indisponível.');
            }
        }

        if (! empty($allowed) && ! in_array($request->ip(), $allowed, true)) {
            Log::warning("{$context}: callback rejeitado — IP não autorizado", ['ip' => $request->ip()]);
            abort(403);
        }
    }

    /**
     * Endpoint JSON para polling do estado da ordem (usado pelo JavaScript da view).
     * Apenas a sessão que iniciou o pagamento pode consultar o estado — previne IDOR.
     */
    public function status(AutovendaOrder $order)
    {
        if (! in_array($order->id, session('gpo_order_ids', []), true)) {
            return response()->json(['error' => 'acesso negado'], 403);
        }

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
            $data['redirect_url'] = URL::signedRoute('store.checkout.confirm', ['order' => $order->id]);
        }

        return response()->json($data);
    }
}
