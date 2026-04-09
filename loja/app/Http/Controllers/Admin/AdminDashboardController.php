<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use App\Models\EquipmentOrder;
use App\Models\FamilyPlanRequest;
use App\Models\Product;
use App\Models\ResellerApplication;
use App\Models\ResellerPurchase;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /** Página de login do painel admin. */
    public function showLogin(Request $request)
    {
        // Bypass SSO: aceite em qualquer ambiente (token enviado pelo SG via ?sg_sso=)
        // Accepted via ?sg_sso= only — the generic ?token= alias was removed to reduce
        // the risk of the secret leaking through browser history or referrer headers.
        $ssoToken = $request->query('sg_sso', '');
        $expected = (string) config('services.sg.admin_token', '');

        if ($ssoToken !== '' && $expected !== '' && hash_equals($expected, $ssoToken)) {
            $request->session()->regenerate();
            $request->session()->put('sg_admin_authenticated', true);
            return redirect()->route('admin.dashboard');
        }

        // Já autenticado? Redireciona directamente para o dashboard.
        if (request()->session()->get('sg_admin_authenticated', false)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /** Processa o formulário de login. */
    public function processLogin(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $expected = (string) config('services.sg.admin_token', '');

        if ($expected !== '' && hash_equals($expected, $request->input('password'))) {
            $request->session()->regenerate();
            $request->session()->put('sg_admin_authenticated', true);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->with('error', 'Palavra-passe incorrecta. Tente novamente.');
    }

    /** Terminar sessão admin. */
    public function logout(Request $request)
    {
        $request->session()->forget('sg_admin_authenticated');
        $request->session()->regenerate();

        return redirect()->route('admin.login');
    }

    public function index()
    {
        $totalOrders = AutovendaOrder::count();
        $paidOrders = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->count();
        $awaitingPayment = AutovendaOrder::where('status', AutovendaOrder::STATUS_AWAITING_PAYMENT)->count();
        $totalRevenueAoa = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->sum('amount_aoa');

        $recentOrders = AutovendaOrder::orderByDesc('id')->limit(8)->get();

        $pendingResellers = ResellerApplication::where('status', ResellerApplication::STATUS_PENDING)->count();
        $totalResellers = ResellerApplication::count();

        // Equipment stats
        $totalProducts      = Product::count();
        $totalEquipOrders   = EquipmentOrder::count();
        $newEquipOrders     = EquipmentOrder::where('status', EquipmentOrder::STATUS_PENDING)->count();
        $totalEquipRevenue  = EquipmentOrder::whereIn('status', [
            EquipmentOrder::STATUS_CONFIRMED,
            EquipmentOrder::STATUS_SHIPPED,
            EquipmentOrder::STATUS_DELIVERED,
        ])->sum('total_aoa');

        // Family/business plan requests
        $pendingFamilyRequests = FamilyPlanRequest::where('status', FamilyPlanRequest::STATUS_PENDING)->count();

        // Active store visitors in the last 5 minutes
        $activeUsers = count(Cache::get('store_online_visitors', []));

        // WiFi code stock
        $availableWifiCodes = WifiCode::where('status', WifiCode::STATUS_AVAILABLE)->count();
        $usedWifiCodes      = WifiCode::where('status', WifiCode::STATUS_USED)->count();

        // Disponíveis por plano individual
        $wifiCodesByPlan = WifiCode::selectRaw('plan_id, COUNT(*) as total')
            ->where('status', WifiCode::STATUS_AVAILABLE)
            ->whereIn('plan_id', ['diario', 'semanal', 'mensal'])
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        return view('admin.dashboard', [
            'totalOrders'        => $totalOrders,
            'paidOrders'         => $paidOrders,
            'awaitingPayment'    => $awaitingPayment,
            'totalRevenueAoa'    => $totalRevenueAoa,
            'recentOrders'       => $recentOrders,
            'pendingResellers'   => $pendingResellers,
            'totalResellers'     => $totalResellers,
            'totalProducts'      => $totalProducts,
            'totalEquipOrders'   => $totalEquipOrders,
            'newEquipOrders'     => $newEquipOrders,
            'totalEquipRevenue'  => $totalEquipRevenue,
            'availableWifiCodes'     => $availableWifiCodes,
            'usedWifiCodes'          => $usedWifiCodes,
            'wifiCodesByPlan'        => $wifiCodesByPlan,
            'pendingFamilyRequests'  => $pendingFamilyRequests,
            'activeUsers'            => $activeUsers,
        ]);
    }

    public function reports()
    {
        // ── Autovenda ────────────────────────────────────────────────────────
        $autoByStatus = AutovendaOrder::selectRaw('status, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $autoByPlan = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)
            ->selectRaw('plan_name, plan_id, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('plan_name', 'plan_id')
            ->orderByDesc('total_amount')
            ->get();

        $autoLatestDays = AutovendaOrder::selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(30)
            ->get();

        $autoTotals = [
            'total_orders'   => AutovendaOrder::count(),
            'paid_orders'    => AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->count(),
            'revenue_aoa'    => AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->sum('amount_aoa'),
            'pending_orders' => AutovendaOrder::where('status', AutovendaOrder::STATUS_AWAITING_PAYMENT)->count(),
        ];

        // ── Vouchers Revendedor (compras normais + venda manual admin) ────────
        $resellerByPlan = ResellerPurchase::where('status', 'completed')
            ->selectRaw('plan_name, plan_slug, SUM(codes_count) as total_codes, SUM(net_amount_aoa) as total_paid, SUM(gross_amount_aoa) as total_gross, SUM(profit_aoa) as total_profit, SUM(tax_aoa) as total_tax')
            ->groupBy('plan_name', 'plan_slug')
            ->orderByDesc('total_codes')
            ->get();

        $resellerByMethod = ResellerPurchase::where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as total, SUM(net_amount_aoa) as total_paid, SUM(codes_count) as total_codes')
            ->groupBy('payment_method')
            ->orderByDesc('total_paid')
            ->get();

        $resellerLatestDays = ResellerPurchase::where('status', 'completed')
            ->selectRaw('DATE(paid_at) as day, COUNT(*) as total, SUM(codes_count) as total_codes, SUM(net_amount_aoa) as total_paid, SUM(profit_aoa) as total_profit, SUM(tax_aoa) as total_tax')
            ->whereNotNull('paid_at')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(30)
            ->get();

        $resellerTotals = ResellerPurchase::where('status', 'completed')
            ->selectRaw('COUNT(*) as total_purchases, SUM(codes_count) as total_codes, SUM(net_amount_aoa) as total_paid, SUM(gross_amount_aoa) as total_gross, SUM(profit_aoa) as total_profit, SUM(tax_aoa) as total_tax')
            ->first();

        // Top 10 revendedores por volume
        $topResellers = ResellerPurchase::where('status', 'completed')
            ->with('application')
            ->select(
                'reseller_application_id',
                DB::raw('SUM(net_amount_aoa) as total_paid'),
                DB::raw('SUM(codes_count) as total_codes'),
                DB::raw('SUM(profit_aoa) as total_profit'),
                DB::raw('SUM(tax_aoa) as total_tax'),
                DB::raw('COUNT(*) as total_purchases')
            )
            ->groupBy('reseller_application_id')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->get();

        // Consolidado geral (autovenda + vouchers revendedor)
        $grandTotalRevenue = ($autoTotals['revenue_aoa'] ?? 0) + ($resellerTotals->total_paid ?? 0);
        $grandTotalTax     = $resellerTotals->total_tax ?? 0; // imposto só no canal revendedor
        $grandTotalOrders  = ($autoTotals['paid_orders'] ?? 0) + ($resellerTotals->total_purchases ?? 0);

        return view('admin.reports', compact(
            'autoByStatus', 'autoByPlan', 'autoLatestDays', 'autoTotals',
            'resellerByPlan', 'resellerByMethod', 'resellerLatestDays', 'resellerTotals',
            'topResellers',
            'grandTotalRevenue', 'grandTotalTax', 'grandTotalOrders'
        ));
    }
}
