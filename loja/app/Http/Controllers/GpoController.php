<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Storage;
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
            $iframeUrl = $this->gpo->createPurchaseToken((float) $order->amount_aoa, $reference, $callbackUrl);
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
     * Callback server-to-server do GPO para compras de revendedores.
     * CSRF-exempt — ver bootstrap/app.php.
     */
    public function resellerCallback(Request $request)
    {
        $data = $request->all();

        Log::info('GPO Revendedor: callback recebido', [
            'ip'   => $request->ip(),
            'body' => $data,
        ]);

        $parsed = $this->gpo->parseCallback($data);

        $merchantRef = $parsed['merchant_ref'];
        if (! $merchantRef) {
            Log::warning('GPO Revendedor: callback sem merchantReference', ['body' => $data]);
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
        $data = $request->all();

        Log::info('GPO Manutenção: callback recebido', [
            'ip'   => $request->ip(),
            'body' => $data,
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
            $application->update([
                'maintenance_paid_year'  => $year,
                'maintenance_paid_month' => $month,
                'maintenance_status'     => ResellerApplication::MAINTENANCE_OK,
            ]);

            // Alocar vouchers bónus e guardar notificação no meta do revendedor
            $allocated = $this->allocateMaintenanceBonusVouchers($application, $year, $month);
            if (! empty($allocated)) {
                $meta = $application->meta ?? [];
                $meta['bonus_notification'] = [
                    'year'      => $year,
                    'month'     => $month,
                    'allocated' => $allocated,
                ];
                $application->update(['meta' => $meta]);
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
     * Aloca vouchers bónus ao revendedor quando a taxa de manutenção é paga.
     * Distribui o valor de bonus_vouchers_aoa proporcionalmente pelos planos activos.
     * Idempotente: identifica o lote com referência BONUS-MNT-{year}-{month}.
     */
    private function allocateMaintenanceBonusVouchers(
        ResellerApplication $application,
        int $year,
        int $month
    ): array {
        $bonus = (int) ($application->bonus_vouchers_aoa ?? 0);
        if ($bonus <= 0) return [];

        $bonusRef = 'BONUS-MNT-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        // Idempotência: já alocado este mês?
        if (ResellerPurchase::where('reseller_application_id', $application->id)
                ->where('payment_reference', $bonusRef)->exists()) {
            return [];
        }

        $plans = VoucherPlan::active()->orderBy('sort_order')->get();
        if ($plans->isEmpty()) return [];

        $perPlanValue = (int) floor($bonus / $plans->count());
        $allocated    = [];

        try {
            DB::transaction(function () use ($plans, $perPlanValue, $application, $bonusRef, &$allocated) {
                foreach ($plans as $plan) {
                    $qty = max(1, (int) floor($perPlanValue / $plan->price_public_aoa));

                    $codes = WifiCode::where('plan_id', $plan->slug)
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->limit($qty)
                        ->get();

                    if ($codes->isEmpty()) continue;

                    $actualQty = $codes->count();
                    $path      = 'resellers/' . $application->id
                                 . '/bonus_' . $plan->slug . '_' . now()->format('Ymd_His') . '.csv';

                    $purchase = ResellerPurchase::create([
                        'reseller_application_id' => $application->id,
                        'voucher_plan_id'          => $plan->id,
                        'plan_slug'                => $plan->slug,
                        'plan_name'                => $plan->name,
                        'quantity'                 => $actualQty,
                        'unit_price_aoa'           => 0,
                        'gross_amount_aoa'         => $plan->price_public_aoa * $actualQty,
                        'discount_percent'         => 100,
                        'net_amount_aoa'           => 0,
                        'codes_count'              => $actualQty,
                        'profit_aoa'               => $plan->price_public_aoa * $actualQty,
                        'tax_aoa'                  => 0,
                        'csv_path'                 => $path,
                        'status'                   => 'completed',
                        'payment_method'           => 'bonus_manutencao',
                        'payment_reference'        => $bonusRef,
                        'paid_at'                  => now(),
                    ]);

                    $codeLines = ['plano,codigo,validade'];
                    foreach ($codes as $wc) {
                        $codeLines[] = "{$plan->name},{$wc->code},{$plan->validity_label}";
                    }
                    Storage::disk('local')->put($path, implode("\n", $codeLines) . "\n");

                    WifiCode::whereIn('id', $codes->pluck('id'))->update([
                        'status'               => 'used',
                        'used_at'              => now(),
                        'reseller_purchase_id' => $purchase->id,
                    ]);

                    $allocated[] = [
                        'plan'        => $plan->name,
                        'qty'         => $actualQty,
                        'purchase_id' => $purchase->id,
                    ];
                }
            });
        } catch (\Throwable $e) {
            Log::error('Bónus manutenção: erro ao alocar vouchers', [
                'reseller_id' => $application->id,
                'error'       => $e->getMessage(),
            ]);
        }

        return $allocated;
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
