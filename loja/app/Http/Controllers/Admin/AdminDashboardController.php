<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use App\Models\EquipmentOrder;
use App\Models\FamilyPlanRequest;
use App\Models\Product;
use App\Models\ResellerApplication;
use App\Models\WifiCode;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /** Página de login do painel admin. */
    public function showLogin()
    {
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
        ]);
    }

    public function reports()
    {
        $byStatus = AutovendaOrder::selectRaw('status, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $latestDays = AutovendaOrder::selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(14)
            ->get();

        return view('admin.reports', [
            'byStatus' => $byStatus,
            'latestDays' => $latestDays,
        ]);
    }
}
