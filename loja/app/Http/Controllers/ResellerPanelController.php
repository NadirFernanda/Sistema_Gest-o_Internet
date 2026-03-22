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
    private const OTP_TTL      = 10;
    private const CART_SESSION = 'reseller_cart'; // [ plan_slug => quantity ]

    // ─────────────────────────────────────────────────────────────
    //  Panel index
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $resellerId  = $request->session()->get('reseller_id');
        $application = null;
        $purchases   = collect();
        $totals      = ['total_invested' => 0, 'vouchers_total' => 0, 'profit_total' => 0];
        $monthlySpend    = 0;
        $estimatedProfit = 0;

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
                $subtotal        = $plan->price_reseller_aoa * $qty;
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

        // Sales report: per-plan breakdown across all purchases
        $salesReport = [];

        if ($resellerId) {
            $application = ResellerApplication::find($resellerId);

            if ($application) {
                $purchases = ResellerPurchase::where('reseller_application_id', $application->id)
                    ->orderByDesc('id')
                    ->paginate(10);

                // Build per-plan sales report from all purchases (not just paginated)
                $allPurchases = ResellerPurchase::where('reseller_application_id', $application->id)->get();
                foreach ($allPurchases as $p) {
                    $totals['total_invested'] += $p->net_amount_aoa;
                    $totals['vouchers_total'] += $p->codes_count;
                    $totals['profit_total']   += ($p->profit_aoa ?? 0);
                    $estimatedProfit          += ($p->profit_aoa ?? ($p->gross_amount_aoa - $p->net_amount_aoa));

                    $slug = $p->plan_slug ?? 'N/A';
                    if (!isset($salesReport[$slug])) {
                        $salesReport[$slug] = [
                            'plan_name'     => $p->plan_name ?? $slug,
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

                // Count distributed (sold to customers) per plan
                if (!empty($salesReport)) {
                    $distributedCounts = WifiCode::whereIn(
                        'reseller_purchase_id',
                        $allPurchases->pluck('id')
                    )->whereNotNull('reseller_distributed_at')
                     ->selectRaw('plan_id, count(*) as cnt')
                     ->groupBy('plan_id')
                     ->pluck('cnt', 'plan_id')
                     ->toArray();

                    foreach ($distributedCounts as $planId => $cnt) {
                        if (isset($salesReport[$planId])) {
                            $salesReport[$planId]['vouchers_sold'] = $cnt;
                        }
                    }
                }

                $monthlySpend = $application->monthlySpendings();
            } else {
                $request->session()->forget('reseller_id');
            }
        }

        $otpEmail   = $request->session()->get('reseller_otp_email');
        $otpPending = !$application && $otpEmail;

        return view('reseller.panel', [
            'application'     => $application,
            'purchases'       => $purchases,
            'totals'          => $totals,
            'monthlySpend'    => $monthlySpend,
            'estimatedProfit' => $estimatedProfit,
            'voucherPlans'    => $voucherPlans,
            'cartItems'       => $cartItems,
            'cartTotal'       => $cartTotal,
            'cartProfit'      => $cartProfit,
            'cartVouchers'    => $cartVouchers,
            'otpPending'      => $otpPending,
            'otpEmail'        => $otpEmail,
            'salesReport'     => $salesReport,
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
            'quantity'  => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $cart = $request->session()->get(self::CART_SESSION, []);
        $cart[$data['plan_slug']] = ($cart[$data['plan_slug']] ?? 0) + (int) $data['quantity'];
        $request->session()->put(self::CART_SESSION, $cart);

        return redirect()->route('reseller.panel')->with('status', 'Plano adicionado ao carrinho.');
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

        return redirect()->route('reseller.panel');
    }

    public function cartClear(Request $request)
    {
        if (!$request->session()->get('reseller_id')) {
            return redirect()->route('reseller.panel');
        }

        $request->session()->forget(self::CART_SESSION);
        return redirect()->route('reseller.panel');
    }

    // ─────────────────────────────────────────────────────────────
    //  Checkout — aloca vouchers reais do stock
    // ─────────────────────────────────────────────────────────────
    public function checkout(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $application = ResellerApplication::findOrFail($resellerId);
        $cart        = $request->session()->get(self::CART_SESSION, []);

        if (empty($cart)) {
            return redirect()->route('reseller.panel')->with('error', 'O carrinho está vazio.');
        }

        $voucherPlans = VoucherPlan::active()->get()->keyBy('slug');

        // Enforce minimum purchase amount
        $cartTotal = 0;
        foreach ($cart as $slug => $qty) {
            $plan = $voucherPlans->get($slug);
            if ($plan) {
                $cartTotal += $plan->price_reseller_aoa * (int) $qty;
            }
        }
        $minPurchase = (int) config('reseller.min_purchase_aoa', 10000);
        if ($cartTotal < $minPurchase) {
            $formatted = number_format($minPurchase, 0, ',', '.');
            return redirect()->route('reseller.panel')
                ->with('error', "O valor mínimo de compra é {$formatted} Kz. O seu carrinho totaliza " . number_format($cartTotal, 0, ',', '.') . " Kz.");
        }

        // Pre-validation: verify stock for each plan
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

        // Allocate codes in a transaction
        $purchases = [];
        try {
            DB::transaction(function () use ($cart, $voucherPlans, $application, &$purchases) {
                foreach ($cart as $slug => $qty) {
                    $plan = $voucherPlans->get($slug);
                    $qty  = (int) $qty;

                    // Lock and fetch codes
                    $codes = WifiCode::where('plan_id', $slug)
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->limit($qty)
                        ->get();

                    if ($codes->count() < $qty) {
                        throw new \RuntimeException("Stock insuficiente para {$plan->name} no momento do checkout.");
                    }

                    $unitPrice  = $plan->price_reseller_aoa;
                    $netAmount  = $unitPrice * $qty;
                    $grossAmt   = $plan->price_public_aoa * $qty;
                    $profit     = $grossAmt - $netAmount;
                    $discPct    = $plan->price_public_aoa > 0
                        ? round((1 - $unitPrice / $plan->price_public_aoa) * 100, 1)
                        : 0;

                    // Build CSV content
                    $codeLines = ['plano,codigo,validade'];
                    foreach ($codes as $wc) {
                        $codeLines[] = "{$plan->name},{$wc->code},{$plan->validity_label}";
                    }
                    $csvContent = implode("\n", $codeLines) . "\n";

                    $path = 'resellers/' . $application->id . '/purchase_' . $slug . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.csv';
                    Storage::disk('local')->put($path, $csvContent);

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
                        'profit_aoa'               => $profit,
                        'csv_path'                 => $path,
                        'status'                   => 'completed',
                        'meta'                     => ['code_preview' => $codes->take(3)->pluck('code')->toArray()],
                    ]);

                    // Mark codes as used, link to this purchase
                    WifiCode::whereIn('id', $codes->pluck('id'))->update([
                        'status'              => 'used',
                        'reseller_purchase_id' => $purchase->id,
                        'used_at'             => now(),
                    ]);

                    $purchases[] = $purchase;
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('reseller.panel')->with('error', $e->getMessage());
        }

        $request->session()->forget(self::CART_SESSION);

        $totalVouchers = array_sum(array_column($purchases, 'codes_count'));
        return redirect()->route('reseller.panel')
            ->with('status', "Compra concluída! {$totalVouchers} voucher(s) alocados. Faça download na tabela abaixo.");
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
}
