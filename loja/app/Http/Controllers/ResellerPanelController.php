<?php

namespace App\Http\Controllers;

use App\Mail\AccountOtpMail;
use App\Models\ResellerApplication;
use App\Models\ResellerPurchase;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResellerPanelController extends Controller
{
    private const OTP_TTL        = 10;
    private const CART_SESSION   = 'reseller_cart';      // [ plan_slug => quantity ] — comprar vouchers
    private const SELL_CART_SESSION = 'reseller_sell_cart'; // [ plan_slug => quantity ] — vender ao cliente final

    // ─────────────────────────────────────────────────────────────
    //  Panel index
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $resellerId  = $request->session()->get('reseller_id');
        $application = null;
        $purchases   = collect();
        $totals      = ['total_invested' => 0, 'vouchers_total' => 0, 'profit_total' => 0, 'vouchers_in_stock' => 0];
        $monthlySpend    = 0;
        $estimatedProfit = 0;

        // Pre-load application before building cart so pricing is mode-aware
        if ($resellerId) {
            $application = ResellerApplication::find($resellerId);
            if (!$application) {
                $request->session()->forget('reseller_id');
                $resellerId = null;
            }
        }

        $voucherPlans = VoucherPlan::active()->get();
        $cart         = $request->session()->get(self::CART_SESSION, []);

        // Enrich cart with plan objects and computed totals
        $cartItems     = [];
        $cartTotal     = 0;
        $cartProfit    = 0;
        $cartVouchers  = 0;
        foreach ($cart as $slug => $qty) {
            $plan = $voucherPlans->firstWhere('slug', $slug);
            if ($plan && $qty > 0) {
                $unitPrice       = $plan->resellerPriceFor($application);
                $subtotal        = $unitPrice * $qty;
                $subtotalPublic  = $plan->price_public_aoa  * $qty;
                $cartItems[]     = [
                    'plan'           => $plan,
                    'qty'            => $qty,
                    'subtotal'       => $subtotal,
                    'subtotalPublic' => $subtotalPublic,
                    'profit'         => $subtotalPublic - $subtotal,
                ];
                $cartTotal    += $subtotal;
                $cartProfit   += ($subtotalPublic - $subtotal);
                $cartVouchers += $qty;
            }
        }

        // Cart tax breakdown (6,5% de retenção sobre o lucro bruto)
        $cartTax        = (int) round($cartProfit * 0.065);
        $cartNetProfit  = $cartProfit - $cartTax;
        $cartPayAmount  = $cartTotal + $cartTax;
        $cartGrossTotal = $cartTotal + $cartProfit;

        // Sales report: per-plan breakdown across all purchases
        $salesReport = [];

        if ($resellerId && $application) {
            $purchases = ResellerPurchase::where('reseller_application_id', $application->id)
                    ->orderByDesc('id')
                    ->paginate(10);

                // Build per-plan sales report from completed purchases only
                $allPurchases = ResellerPurchase::where('reseller_application_id', $application->id)
                    ->where('status', 'completed')
                    ->get();

                // Backfill plan_slug/plan_name for legacy purchases that have NULL values
                $planMap = VoucherPlan::all()->keyBy('slug');
                $purchasesWithNullSlug = $allPurchases->whereNull('plan_slug');
                if ($purchasesWithNullSlug->isNotEmpty()) {
                    $wifiCodePlanIds = WifiCode::whereIn('reseller_purchase_id', $purchasesWithNullSlug->pluck('id'))
                        ->selectRaw('reseller_purchase_id, plan_id')
                        ->groupBy('reseller_purchase_id', 'plan_id')
                        ->get()
                        ->keyBy('reseller_purchase_id');

                    foreach ($purchasesWithNullSlug as $p) {
                        $wc = $wifiCodePlanIds->get($p->id);
                        if ($wc) {
                            $detectedSlug = $wc->plan_id;
                            $detectedName = optional($planMap->get($detectedSlug))->name ?? $detectedSlug;
                            // Persist correct values so the DB is self-healing
                            $p->update(['plan_slug' => $detectedSlug, 'plan_name' => $detectedName]);
                            $p->plan_slug = $detectedSlug;
                            $p->plan_name = $detectedName;
                        }
                    }
                }

                foreach ($allPurchases as $p) {
                    $totals['total_invested'] += $p->net_amount_aoa;
                    $totals['vouchers_total'] += $p->codes_count;
                    $totals['profit_total']   += ($p->profit_aoa ?? 0);
                    $estimatedProfit          += ($p->profit_aoa ?? ($p->gross_amount_aoa - $p->net_amount_aoa));

                    $slug = $p->plan_slug ?? 'desconhecido';
                    if (!isset($salesReport[$slug])) {
                        $planLabel = $p->plan_name ?? optional($planMap->get($slug))->name ?? $slug;
                        $salesReport[$slug] = [
                            'plan_name'       => $planLabel,
                            'vouchers_bought' => 0,
                            'vouchers_sold'   => 0,
                            'invested_aoa'    => 0,
                            'profit_aoa'      => 0,
                        ];
                    }
                    $salesReport[$slug]['vouchers_bought'] += $p->codes_count;
                    $salesReport[$slug]['invested_aoa']    += $p->net_amount_aoa;
                    $salesReport[$slug]['profit_aoa']      += ($p->profit_aoa ?? 0);
                }

                // Count distributed + unsold per plan for the report and the sell button
                if (!empty($salesReport) || $allPurchases->isNotEmpty()) {
                    $completedIds = $allPurchases->pluck('id');

                    $distributedCounts = WifiCode::whereIn('reseller_purchase_id', $completedIds)
                        ->whereNotNull('reseller_distributed_at')
                        ->selectRaw('plan_id, count(*) as cnt')
                        ->groupBy('plan_id')
                        ->pluck('cnt', 'plan_id')
                        ->toArray();

                    $unsoldCounts = WifiCode::whereIn('reseller_purchase_id', $completedIds)
                        ->whereNull('reseller_distributed_at')
                        ->selectRaw('plan_id, count(*) as cnt')
                        ->groupBy('plan_id')
                        ->pluck('cnt', 'plan_id')
                        ->toArray();

                    foreach ($distributedCounts as $planId => $cnt) {
                        if (isset($salesReport[$planId])) {
                            $salesReport[$planId]['vouchers_sold'] = $cnt;
                        }
                    }

                    foreach ($unsoldCounts as $planId => $cnt) {
                        if (isset($salesReport[$planId])) {
                            $salesReport[$planId]['vouchers_in_stock'] = $cnt;
                        }
                    }

                    $totals['vouchers_in_stock'] = array_sum($unsoldCounts);
                }

                $monthlySpend = $application->monthlySales();
        }

        $topSellers = collect([]);
        $myRank     = null;
        if ($application) {
            $topSellers = ResellerApplication::topSellersThisMonth(10);
            $pos = $topSellers->search(fn($r) => $r->reseller_id === $application->id);
            $myRank = $pos !== false ? $pos + 1 : null;
        }

        $otpEmail   = $request->session()->get('reseller_otp_email');
        $otpPending = !$application && $otpEmail;

        $storePlansConfig = collect(config('store_plans.individual', []))->keyBy('id');

        return view('reseller.panel', [
            'application'      => $application,
            'purchases'        => $purchases,
            'totals'           => $totals,
            'monthlySpend'     => $monthlySpend,
            'estimatedProfit'  => $estimatedProfit,
            'voucherPlans'     => $voucherPlans,
            'cartItems'        => $cartItems,
            'cartTotal'        => $cartTotal,
            'cartProfit'       => $cartProfit,
            'cartTax'          => $cartTax,
            'cartNetProfit'    => $cartNetProfit,
            'cartPayAmount'    => $cartPayAmount,
            'cartGrossTotal'   => $cartGrossTotal,
            'cartVouchers'     => $cartVouchers,
            'otpPending'       => $otpPending,
            'otpEmail'         => $otpEmail,
            'salesReport'      => $salesReport,
            'minPurchaseAoa'   => (int) config('reseller.min_purchase_aoa', 10000),
            'storePlansConfig' => $storePlansConfig,
            'topSellers'       => $topSellers,
            'myRank'           => $myRank,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  OTP login
    // ─────────────────────────────────────────────────────────────
    public function login(Request $request)
    {
        $data  = $request->validate(['email' => ['required', 'email', 'max:254']]);
        $email = strtolower(trim($data['email']));

        $application = ResellerApplication::where('email', $email)
            ->where('status', ResellerApplication::STATUS_APPROVED)
            ->first();

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('reseller_otp_email',      $email);
        $request->session()->put('reseller_otp_code',       $otp);
        $request->session()->put('reseller_otp_expires_at', now()->addMinutes(self::OTP_TTL)->toIso8601String());
        $request->session()->forget('reseller_id');

        if ($application) {
            Mail::to($email)->send(new AccountOtpMail($otp));
        }

        return redirect()->route('reseller.panel')
            ->with('status', 'Se o endereço indicado corresponde a um revendedor aprovado, receberá um código de verificação em breve.');
    }

    public function verify(Request $request)
    {
        $data           = $request->validate(['otp' => ['required', 'string', 'digits:6']]);
        $sessionEmail   = $request->session()->get('reseller_otp_email');
        $sessionCode    = $request->session()->get('reseller_otp_code');
        $sessionExpires = $request->session()->get('reseller_otp_expires_at');

        if (!$sessionEmail || !$sessionCode || !$sessionExpires) {
            return redirect()->route('reseller.panel')->with('error', 'Sessão expirada. Introduza novamente o seu email.');
        }

        if (Carbon::parse($sessionExpires)->isPast()) {
            $request->session()->forget(['reseller_otp_email', 'reseller_otp_code', 'reseller_otp_expires_at']);
            return redirect()->route('reseller.panel')->with('error', 'O código expirou. Introduza o seu email de novo para receber outro código.');
        }

        if (!hash_equals($sessionCode, $data['otp'])) {
            return redirect()->route('reseller.panel')->with('error', 'Código incorreto. Verifique o email e tente novamente.');
        }

        $application = ResellerApplication::where('email', $sessionEmail)
            ->where('status', ResellerApplication::STATUS_APPROVED)
            ->first();

        $request->session()->forget(['reseller_otp_email', 'reseller_otp_code', 'reseller_otp_expires_at']);

        if (!$application) {
            return redirect()->route('reseller.panel')->with('error', 'Nenhum revendedor aprovado encontrado para este endereço de email.');
        }

        $request->session()->regenerate();
        $request->session()->put('reseller_id', $application->id);
        return redirect()->route('reseller.panel');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['reseller_id', 'reseller_otp_email', 'reseller_otp_code', 'reseller_otp_expires_at', self::CART_SESSION]);
        return redirect()->route('reseller.panel');
    }

    // ─────────────────────────────────────────────────────────────
    //  Carrinho
    // ─────────────────────────────────────────────────────────────
    public function cartAdd(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:voucher_plans,slug'],
            'quantity'  => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->session()->get(self::CART_SESSION, []);
        $cart[$data['plan_slug']] = ($cart[$data['plan_slug']] ?? 0) + (int) $data['quantity'];
        $request->session()->put(self::CART_SESSION, $cart);

        return redirect()->route('reseller.panel')->with('status', 'Plano adicionado ao carrinho.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Carrinho de compra — adicionar múltiplos planos de uma vez
    // ─────────────────────────────────────────────────────────────
    public function cartAddAll(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $data = $request->validate([
            'plans'   => ['required', 'array'],
            'plans.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $validSlugs = \App\Models\VoucherPlan::pluck('slug')->flip();
        $cart  = $request->session()->get(self::CART_SESSION, []);
        $added = 0;

        foreach ($data['plans'] as $slug => $qty) {
            $qty = (int) $qty;
            if ($qty > 0 && $validSlugs->has($slug)) {
                $cart[$slug] = ($cart[$slug] ?? 0) + $qty;
                $added++;
            }
        }

        $request->session()->put(self::CART_SESSION, $cart);

        if ($added === 0) {
            return redirect()->route('reseller.panel')->with('error', 'Insira pelo menos uma quantidade maior que zero.');
        }

        return redirect()->route('reseller.panel')->with('status', $added . ' plano(s) adicionados ao carrinho.');
    }

    public function cartRemove(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $data = $request->validate(['plan_slug' => ['required', 'string']]);
        $cart = $request->session()->get(self::CART_SESSION, []);
        unset($cart[$data['plan_slug']]);
        $request->session()->put(self::CART_SESSION, $cart);

        return redirect()->back();
    }

    public function cartClear(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $request->session()->forget(self::CART_SESSION);
        return redirect()->back();
    }

    // ─────────────────────────────────────────────────────────────
    //  Checkout — reserva vouchers do stock e aguarda pagamento
    // ─────────────────────────────────────────────────────────────
    public function checkout(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        // If there's already a pending order, send to payment page
        if ($request->session()->has('reseller_pending_order')) {
            return redirect()->route('reseller.panel.payment');
        }

        $application = ResellerApplication::findOrFail($resellerId);
        $cart        = $request->session()->get(self::CART_SESSION, []);

        if (empty($cart)) {
            return redirect()->route('reseller.panel')->with('error', 'O carrinho está vazio.');
        }

        $voucherPlans = VoucherPlan::active()->get()->keyBy('slug');

        // Enforce minimum purchase amount (using mode-aware pricing)
        $cartTotal = 0;
        foreach ($cart as $slug => $qty) {
            $plan = $voucherPlans->get($slug);
            if ($plan) {
                $cartTotal += $plan->resellerPriceFor($application) * (int) $qty;
            }
        }
        $minPurchase = (int) config('reseller.min_purchase_aoa', 10000);
        if ($cartTotal < $minPurchase) {
            $formatted = number_format($minPurchase, 0, ',', '.');
            return redirect()->route('reseller.panel')
                ->with('error', "O valor mínimo de compra é {$formatted} Kz. O seu carrinho totaliza " . number_format($cartTotal, 0, ',', '.') . " Kz.");
        }

        // Pre-validation: verify available stock for each plan
        foreach ($cart as $slug => $qty) {
            $plan = $voucherPlans->get($slug);
            if (!$plan) {
                return redirect()->route('reseller.panel')->with('error', "Plano \"$slug\" não encontrado.");
            }
            $stock = WifiCode::where('plan_id', $slug)->where('status', 'available')->count();
            if ($stock < $qty) {
                return redirect()->route('reseller.panel')
                    ->with('error', "Stock insuficiente para \"{$plan->name}\": pediu {$qty}, disponível {$stock}.");
            }
        }

        // Reserve codes and create pending purchases (DB transaction)
        $purchaseIds = [];
        try {
            DB::transaction(function () use ($cart, $voucherPlans, $application, &$purchaseIds) {
                foreach ($cart as $slug => $qty) {
                    $plan = $voucherPlans->get($slug);
                    $qty  = (int) $qty;

                    $codes = WifiCode::where('plan_id', $slug)
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->limit($qty)
                        ->get();

                    if ($codes->count() < $qty) {
                        throw new \RuntimeException("Stock insuficiente para {$plan->name} no momento do checkout.");
                    }

                    $unitPrice   = $plan->resellerPriceFor($application);
                    $grossAmt    = $plan->price_public_aoa * $qty;
                    $resellerCost = $unitPrice * $qty;
                    $grossProfit = $grossAmt - $resellerCost;
                    $taxAoa      = (int) round($grossProfit * 0.065); // 6,5% retido para impostos
                    $netProfit   = $grossProfit - $taxAoa;            // lucro líquido após impostos
                    $netAmount   = $resellerCost + $taxAoa;           // valor a pagar = custo + impostos
                    $discPct     = $plan->price_public_aoa > 0
                        ? round((1 - $unitPrice / $plan->price_public_aoa) * 100, 1)
                        : 0;

                    $path = 'resellers/' . $application->id . '/purchase_' . $slug . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.csv';

                    $purchase = ResellerPurchase::create([
                        'reseller_application_id' => $application->id,
                        'voucher_plan_id'          => $plan->id,
                        'plan_slug'                => $slug,
                        'plan_name'                => $plan->name,
                        'quantity'                 => $qty,
                        'unit_price_aoa'           => $unitPrice,
                        'gross_amount_aoa'         => $grossAmt,
                        'discount_percent'         => $discPct,
                        'net_amount_aoa'           => $netAmount,
                        'codes_count'              => $qty,
                        'profit_aoa'               => $netProfit,
                        'tax_aoa'                  => $taxAoa,
                        'csv_path'                 => $path,
                        'status'                   => 'pending',
                        'meta'                     => ['code_preview' => $codes->take(3)->pluck('code')->toArray()],
                    ]);

                    // Reserve codes — linked to this purchase, not yet 'used'
                    WifiCode::whereIn('id', $codes->pluck('id'))->update([
                        'status'               => 'reserved',
                        'reseller_purchase_id' => $purchase->id,
                    ]);

                    $purchaseIds[] = $purchase->id;
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('reseller.panel')->with('error', $e->getMessage());
        }

        // Clear cart — the pending order now holds the reservation
        $request->session()->forget(self::CART_SESSION);
        $request->session()->put('reseller_pending_order', $purchaseIds);

        return redirect()->route('reseller.panel.payment');
    }

    // ─────────────────────────────────────────────────────────────
    //  Página de pagamento
    // ─────────────────────────────────────────────────────────────
    public function showPayment(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $purchaseIds = $request->session()->get('reseller_pending_order', []);
        if (empty($purchaseIds)) {
            return redirect()->route('reseller.panel')->with('error', 'Nenhum pedido pendente encontrado.');
        }

        $application = ResellerApplication::findOrFail($resellerId);
        $purchases   = ResellerPurchase::whereIn('id', $purchaseIds)
            ->where('reseller_application_id', $application->id)
            ->where('status', 'pending')
            ->get();

        if ($purchases->isEmpty()) {
            $request->session()->forget('reseller_pending_order');
            return redirect()->route('reseller.panel')->with('error', 'Pedido já processado ou expirado.');
        }

        $total = $purchases->sum('net_amount_aoa');

        // Generate a deterministic Multicaixa reference from the first purchase ID
        $mcxEntity = '00372';
        $mcxRef    = str_pad($purchases->first()->id * 97 + 300000, 9, '0', STR_PAD_LEFT);

        return view('reseller.checkout', compact('application', 'purchases', 'total', 'mcxEntity', 'mcxRef'));
    }

    // ─────────────────────────────────────────────────────────────
    //  Retomar pagamento pendente (sessão expirou após checkout)
    // ─────────────────────────────────────────────────────────────
    public function resumePayment(Request $request, ResellerPurchase $purchase)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        // Security: ensure this purchase belongs to the logged-in reseller and is still pending
        if ((int) $purchase->reseller_application_id !== (int) $resellerId || $purchase->status !== 'pending') {
            return redirect()->route('reseller.panel')->with('error', 'Pedido não encontrado ou já processado.');
        }

        // Restore all pending purchases from this checkout batch into the session
        // (a single checkout may create multiple purchases, one per plan)
        $pendingIds = ResellerPurchase::where('reseller_application_id', $resellerId)
            ->where('status', 'pending')
            ->pluck('id')
            ->toArray();

        $request->session()->put('reseller_pending_order', $pendingIds);

        return redirect()->route('reseller.panel.payment');
    }

    // ─────────────────────────────────────────────────────────────
    //  Confirmação de pagamento → transfere vouchers para o agente
    // ─────────────────────────────────────────────────────────────
    public function confirmPayment(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $purchaseIds = $request->session()->get('reseller_pending_order', []);
        if (empty($purchaseIds)) {
            return redirect()->route('reseller.panel')->with('error', 'Nenhum pedido pendente.');
        }

        $application = ResellerApplication::findOrFail($resellerId);

        $data = $request->validate([
            'payment_method'    => ['required', 'string', 'in:multicaixa,transferencia,multicaixa_express'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $method    = $data['payment_method'];
        $reference = trim($data['payment_reference'] ?? '')
            ?: ('SIM-' . now()->format('YmdHis') . '-' . $application->id);

        // Load plan validity labels for the CSV
        $planSlugs = ResellerPurchase::whereIn('id', $purchaseIds)->pluck('plan_slug')->toArray();
        $planMap   = VoucherPlan::whereIn('slug', $planSlugs)->get()->keyBy('slug');

        try {
            DB::transaction(function () use ($purchaseIds, $application, $method, $reference, $planMap) {
                $purchases = ResellerPurchase::whereIn('id', $purchaseIds)
                    ->where('reseller_application_id', $application->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->get();

                foreach ($purchases as $purchase) {
                    $codes = WifiCode::where('reseller_purchase_id', $purchase->id)
                        ->where('status', 'reserved')
                        ->get();

                    if ($codes->isEmpty()) {
                        throw new \RuntimeException("Códigos não encontrados para o plano {$purchase->plan_name}.");
                    }

                    $validityLabel = optional($planMap->get($purchase->plan_slug))->validity_label
                        ?? $purchase->plan_slug;

                    // Build and store CSV
                    $codeLines = ['plano,codigo,validade'];
                    foreach ($codes as $wc) {
                        $codeLines[] = "{$purchase->plan_name},{$wc->code},{$validityLabel}";
                    }
                    Storage::disk('local')->put($purchase->csv_path, implode("\n", $codeLines) . "\n");

                    // Mark codes as used
                    WifiCode::whereIn('id', $codes->pluck('id'))->update([
                        'status'  => 'used',
                        'used_at' => now(),
                    ]);

                    // Finalise purchase
                    $purchase->update([
                        'status'            => 'completed',
                        'payment_method'    => $method,
                        'payment_reference' => $reference,
                        'paid_at'           => now(),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('reseller.panel.payment')->with('error', $e->getMessage());
        }

        $totalVouchers = ResellerPurchase::whereIn('id', $purchaseIds)->sum('codes_count');
        $request->session()->forget(['reseller_pending_order']);

        return redirect()->route('reseller.panel')
            ->with('status', "✅ Pagamento confirmado! {$totalVouchers} voucher(s) transferidos para a sua conta. Faça download na tabela abaixo.");
    }

    // ─────────────────────────────────────────────────────────────
    //  Cancelar pagamento — liberta os vouchers reservados
    // ─────────────────────────────────────────────────────────────
    public function cancelPayment(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $purchaseIds = $request->session()->get('reseller_pending_order', []);
        if (!empty($purchaseIds)) {
            $application = ResellerApplication::findOrFail($resellerId);
            DB::transaction(function () use ($purchaseIds, $application) {
                $purchases = ResellerPurchase::whereIn('id', $purchaseIds)
                    ->where('reseller_application_id', $application->id)
                    ->where('status', 'pending')
                    ->get();

                foreach ($purchases as $purchase) {
                    WifiCode::where('reseller_purchase_id', $purchase->id)
                        ->where('status', 'reserved')
                        ->update([
                            'status'               => 'available',
                            'reseller_purchase_id' => null,
                        ]);
                    $purchase->delete();
                }
            });
        }

        $request->session()->forget('reseller_pending_order');
        return redirect()->route('reseller.panel')
            ->with('error', 'Compra cancelada. Os vouchers reservados foram libertados e o seu carrinho foi restaurado.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Taxa de manutenção — página de pagamento
    // ─────────────────────────────────────────────────────────────
    public function showMaintenancePayment(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $application = ResellerApplication::findOrFail($resellerId);

        if (!$application->maintenanceDueThisMonth()) {
            return redirect()->route('reseller.panel')
                ->with('status', 'Não há taxa de manutenção em dívida no mês actual.');
        }

        $amount    = $application->maintenanceFeeAoa();
        $mcxEntity = '00372';
        $mcxRef    = str_pad($application->id * 31 + 500000, 9, '0', STR_PAD_LEFT);

        $token = bin2hex(random_bytes(16));
        $request->session()->put('maintenance_payment_token', $token);

        return view('reseller.maintenance-payment', compact('application', 'amount', 'mcxEntity', 'mcxRef', 'token'));
    }

    // ─────────────────────────────────────────────────────────────
    //  Taxa de manutenção — confirmar pagamento
    // ─────────────────────────────────────────────────────────────
    public function confirmMaintenancePayment(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $application = ResellerApplication::findOrFail($resellerId);

        if (!$application->maintenanceDueThisMonth()) {
            return redirect()->route('reseller.panel')
                ->with('error', 'Não há taxa de manutenção em dívida no mês actual.');
        }

        $data = $request->validate([
            'payment_method'    => ['required', 'string', 'in:multicaixa,transferencia,multicaixa_express'],
            'payment_reference' => ['nullable', 'string', 'max:100', 'required_if:payment_method,transferencia,multicaixa_express'],
            'payment_token'     => ['required', 'string', 'size:32'],
        ]);

        $sessionToken = $request->session()->pull('maintenance_payment_token');
        if (!$sessionToken || !hash_equals($sessionToken, $data['payment_token'])) {
            return redirect()->route('reseller.maintenance.payment')
                ->with('error', 'A confirmação expirou. Atualize a página e tente novamente.');
        }

        $application->update([
            'maintenance_paid_year'  => now()->year,
            'maintenance_paid_month' => now()->month,
            'maintenance_status'    => ResellerApplication::MAINTENANCE_OK,
        ]);

        return redirect()->route('reseller.panel')
            ->with('status', '✅ Pagamento da taxa de manutenção registado com sucesso! Obrigado.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Compra legacy (mantida para compatibilidade)
    // ─────────────────────────────────────────────────────────────
    public function storePurchase(Request $request)
    {
        // Redirect to new checkout flow
        return redirect()->route('reseller.panel')->with('error', 'Por favor use o novo sistema de carrinho para comprar vouchers.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Download CSV
    // ─────────────────────────────────────────────────────────────
    public function downloadCsv(Request $request, ResellerPurchase $purchase)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId || $purchase->reseller_application_id !== $resellerId) abort(403);

        if (!Storage::disk('local')->exists($purchase->csv_path)) abort(404, 'CSV não encontrado.');

        return response()->streamDownload(function () use ($purchase) {
            echo Storage::disk('local')->get($purchase->csv_path);
        }, 'vouchers_' . ($purchase->plan_slug ?? 'compra') . '_' . $purchase->id . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Download vouchers como lista de texto simples
    // ─────────────────────────────────────────────────────────────
    public function downloadVouchers(Request $request, ResellerPurchase $purchase)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId || $purchase->reseller_application_id !== $resellerId) abort(403);

        // Fetch the actual wifi codes linked to this purchase
        $codes = WifiCode::where('reseller_purchase_id', $purchase->id)->get();

        if ($codes->isEmpty() && Storage::disk('local')->exists($purchase->csv_path ?? '')) {
            // Fallback to CSV file for legacy purchases
            return $this->downloadCsv($request, $purchase);
        }

        $lines = ['Plano,Código,Validade'];
        foreach ($codes as $wc) {
            $validityLabel = $purchase->plan_name ?? ($purchase->plan_slug ?? 'N/A');
            $lines[] = "{$purchase->plan_name},{$wc->code},{$validityLabel}";
        }

        $filename = 'vouchers_' . ($purchase->plan_slug ?? 'compra') . '_' . $purchase->id . '.csv';
        return response(implode("\n", $lines) . "\n")
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    // ─────────────────────────────────────────────────────────────
    //  Ver e gerir códigos de uma compra (distribuição ao cliente)
    // ─────────────────────────────────────────────────────────────
    public function showCodes(Request $request, ResellerPurchase $purchase)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId || $purchase->reseller_application_id !== $resellerId) abort(403);

        $codes = WifiCode::where('reseller_purchase_id', $purchase->id)
            ->orderBy('reseller_distributed_at')
            ->orderBy('id')
            ->get();

        $totalCodes    = $codes->count();
        $inStock       = $codes->whereNull('reseller_distributed_at')->count();
        $distributed   = $codes->whereNotNull('reseller_distributed_at')->count();

        return view('reseller.stock', [
            'purchase'    => $purchase,
            'codes'       => $codes,
            'totalCodes'  => $totalCodes,
            'inStock'     => $inStock,
            'distributed' => $distributed,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Marcar voucher como vendido ao cliente final
    // ─────────────────────────────────────────────────────────────
    public function distributeVoucher(Request $request, WifiCode $wifiCode)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) abort(403);

        // Verify ownership via the purchase FK
        $purchase = ResellerPurchase::find($wifiCode->reseller_purchase_id);
        if (!$purchase || $purchase->reseller_application_id !== $resellerId) abort(403);

        if ($wifiCode->reseller_distributed_at) {
            return back()->with('error', 'Este voucher já foi marcado como vendido.');
        }

        $data = $request->validate([
            'customer_ref' => ['nullable', 'string', 'max:200'],
        ]);

        $wifiCode->update([
            'reseller_distributed_at' => now(),
            'reseller_customer_ref'   => $data['customer_ref'] ?? null,
        ]);

        return back()->with('status', 'Voucher marcado como vendido ao cliente.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Cancelar marcação (em caso de erro)
    // ─────────────────────────────────────────────────────────────
    public function undistributeVoucher(Request $request, WifiCode $wifiCode)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) abort(403);

        $purchase = ResellerPurchase::find($wifiCode->reseller_purchase_id);
        if (!$purchase || $purchase->reseller_application_id !== $resellerId) abort(403);

        $wifiCode->update([
            'reseller_distributed_at' => null,
            'reseller_customer_ref'   => null,
        ]);

        return back()->with('status', 'Marcação cancelada — voucher devolvido ao stock.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Download PDF dos vouchers de uma compra
    // ─────────────────────────────────────────────────────────────
    public function downloadPdf(Request $request, ResellerPurchase $purchase)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId || $purchase->reseller_application_id !== $resellerId) abort(403);

        $application = ResellerApplication::findOrFail($resellerId);

        $codes = WifiCode::where('reseller_purchase_id', $purchase->id)
            ->orderBy('reseller_distributed_at')
            ->orderBy('id')
            ->get();

        $totalCodes  = $codes->count();
        $inStock     = $codes->whereNull('reseller_distributed_at')->count();
        $distributed = $codes->whereNotNull('reseller_distributed_at')->count();
        $voucherPlan = VoucherPlan::where('slug', $purchase->plan_slug)->first();

        $html = view('pdf.vouchers-revendedor', compact(
            'purchase', 'application', 'codes', 'voucherPlan',
            'totalCodes', 'inStock', 'distributed'
        ))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'vouchers_' . ($purchase->plan_slug ?? 'compra') . '_' . $purchase->id . '.pdf';

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ─────────────────────────────────────────────────────────────
    //  Página de vendas ao cliente final — catálogo de stock do agente
    // ─────────────────────────────────────────────────────────────
    public function showSellPage(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $application = ResellerApplication::findOrFail($resellerId);

        // Vouchers disponíveis para venda (por plano)
        $purchaseIds = ResellerPurchase::where('reseller_application_id', $resellerId)
            ->where('status', 'completed')
            ->pluck('id');

        // Stock por plano: apenas os não vendidos
        $stockByPlan = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
            ->whereNull('reseller_distributed_at')
            ->selectRaw('plan_id, count(*) as qty')
            ->groupBy('plan_id')
            ->pluck('qty', 'plan_id');

        $voucherPlans = VoucherPlan::all()->keyBy('slug');

        // Carrinho de venda ao cliente — planos e quantidades
        $sellCart  = $request->session()->get(self::SELL_CART_SESSION, []);
        $sellItems = [];
        $sellTotal = 0;

        foreach ($sellCart as $slug => $qty) {
            $plan      = $voucherPlans->get($slug);
            $available = $stockByPlan->get($slug, 0);
            if ($plan && $qty > 0 && $available > 0) {
                $qty       = min($qty, $available); // cap at available stock
                $subtotal  = $plan->price_public_aoa * $qty;
                $sellItems[] = [
                    'plan'      => $plan,
                    'qty'       => $qty,
                    'available' => $available,
                    'subtotal'  => $subtotal,
                ];
                $sellTotal += $subtotal;
            }
        }

        // Vendas recentes (últimas 20)
        $recentSales = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
            ->whereNotNull('reseller_distributed_at')
            ->orderByDesc('reseller_distributed_at')
            ->take(20)
            ->get();

        $totalInStock = $stockByPlan->sum();
        $totalSold = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
            ->whereNotNull('reseller_distributed_at')
            ->count();

        $pendingPurchases = ResellerPurchase::where('reseller_application_id', $resellerId)
            ->where('status', 'pending')
            ->get();

        return view('reseller.sell', [
            'application'      => $application,
            'stockByPlan'      => $stockByPlan,
            'voucherPlans'     => $voucherPlans,
            'sellItems'        => $sellItems,
            'sellTotal'        => $sellTotal,
            'totalInStock'     => $totalInStock,
            'totalSold'        => $totalSold,
            'recentSales'      => $recentSales,
            'pendingPurchases' => $pendingPurchases,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Carrinho de venda ao cliente — adicionar plano
    // ─────────────────────────────────────────────────────────────
    public function sellCartAdd(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:voucher_plans,slug'],
            'quantity'  => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $cart = $request->session()->get(self::SELL_CART_SESSION, []);
        $cart[$data['plan_slug']] = ($cart[$data['plan_slug']] ?? 0) + (int) $data['quantity'];
        $request->session()->put(self::SELL_CART_SESSION, $cart);

        return redirect()->route('reseller.sell')->with('status', 'Plano adicionado ao carrinho.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Carrinho de venda — adicionar múltiplos planos de uma vez
    // ─────────────────────────────────────────────────────────────
    public function sellCartAddAll(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $data = $request->validate([
            'plans'   => ['required', 'array'],
            'plans.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $validSlugs = \App\Models\VoucherPlan::pluck('slug')->flip();
        $cart  = $request->session()->get(self::SELL_CART_SESSION, []);
        $added = 0;

        foreach ($data['plans'] as $slug => $qty) {
            $qty = (int) $qty;
            if ($qty > 0 && $validSlugs->has($slug)) {
                $cart[$slug] = ($cart[$slug] ?? 0) + $qty;
                $added++;
            }
        }

        $request->session()->put(self::SELL_CART_SESSION, $cart);

        if ($added === 0) {
            return redirect()->route('reseller.sell')->with('error', 'Insira pelo menos uma quantidade maior que zero.');
        }

        return redirect()->route('reseller.sell')->with('status', $added . ' plano(s) adicionados ao carrinho de venda.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Carrinho de venda ao cliente — remover plano
    // ─────────────────────────────────────────────────────────────
    public function sellCartRemove(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $data = $request->validate(['plan_slug' => ['required', 'string']]);
        $cart = $request->session()->get(self::SELL_CART_SESSION, []);
        unset($cart[$data['plan_slug']]);
        $request->session()->put(self::SELL_CART_SESSION, $cart);

        return redirect()->back();
    }

    // ─────────────────────────────────────────────────────────────
    //  Carrinho de venda ao cliente — limpar
    // ─────────────────────────────────────────────────────────────
    public function sellCartClear(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $request->session()->forget(self::SELL_CART_SESSION);
        return redirect()->back();
    }

    // ─────────────────────────────────────────────────────────────
    //  Processar venda ao cliente — aloca vouchers automaticamente
    // ─────────────────────────────────────────────────────────────
    public function processSale(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $data = $request->validate([
            'customer_ref' => ['nullable', 'string', 'max:200'],
        ]);

        $application = ResellerApplication::findOrFail($resellerId);
        $sellCart    = $request->session()->get(self::SELL_CART_SESSION, []);

        if (empty($sellCart)) {
            return redirect()->route('reseller.sell')->with('error', 'O carrinho de venda está vazio.');
        }

        // Vouchers disponíveis (only from completed purchases, not yet distributed)
        $purchaseIds = ResellerPurchase::where('reseller_application_id', $resellerId)
            ->where('status', 'completed')
            ->pluck('id');

        $voucherPlans = VoucherPlan::all()->keyBy('slug');

        // Validate stock for each plan in the sell cart
        foreach ($sellCart as $slug => $qty) {
            $available = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
                ->where('plan_id', $slug)
                ->whereNull('reseller_distributed_at')
                ->count();

            if ($available < $qty) {
                $planName = optional($voucherPlans->get($slug))->name ?? $slug;
                return redirect()->route('reseller.sell')
                    ->with('error', "Stock insuficiente para \"{$planName}\": pediu {$qty}, disponível {$available}.");
            }
        }

        // Allocate vouchers for each plan and mark as distributed
        $now         = now();
        $customerRef = $data['customer_ref'] ?? null;
        $allCodes    = collect();

        try {
            DB::transaction(function () use ($sellCart, $purchaseIds, $customerRef, $now, &$allCodes) {
                foreach ($sellCart as $slug => $qty) {
                    $qty = (int) $qty;

                    $codes = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
                        ->where('plan_id', $slug)
                        ->whereNull('reseller_distributed_at')
                        ->lockForUpdate()
                        ->limit($qty)
                        ->get();

                    if ($codes->count() < $qty) {
                        throw new \RuntimeException("Stock insuficiente para o plano \"{$slug}\" no momento da venda.");
                    }

                    WifiCode::whereIn('id', $codes->pluck('id'))->update([
                        'reseller_distributed_at' => $now,
                        'reseller_customer_ref'   => $customerRef,
                    ]);

                    $allCodes = $allCodes->concat($codes->pluck('id'));
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('reseller.sell')->with('error', $e->getMessage());
        }

        // Clear sell cart
        $request->session()->forget(self::SELL_CART_SESSION);

        // Reload codes with updated data for PDF
        $codes       = WifiCode::whereIn('id', $allCodes)->get();
        $codesByPlan = $codes->groupBy('plan_id');

        // Generate PDF
        $html = view('pdf.venda-revendedor', [
            'application'  => $application,
            'codesByPlan'  => $codesByPlan,
            'voucherPlans' => $voucherPlans,
            'customerRef'  => $customerRef,
            'totalCodes'   => $codes->count(),
            'saleDate'     => $now,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'venda_' . $codes->count() . 'vouchers_' . $now->format('Ymd_His') . '.pdf';

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
