<?php

namespace App\Http\Controllers;

use App\Models\ResellerStaff;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPanelController extends Controller
{
    const SESS = 'staff_member_id';

    private function getStaff(Request $request): ?ResellerStaff
    {
        $id = $request->session()->get(self::SESS);
        if (!$id) return null;
        return ResellerStaff::with('application')->find($id);
    }

    public function index(Request $request)
    {
        $staff = $this->getStaff($request);

        if (!$staff) {
            return view('staff.panel', ['staff' => null, 'suspended' => false]);
        }

        if (!$staff->isActive()) {
            $request->session()->forget(self::SESS);
            return view('staff.panel', ['staff' => null, 'suspended' => true]);
        }

        $application = $staff->application;
        $purchaseIds = $application->purchases()->pluck('id');

        // Available stock from AR, by plan
        $availableByPlan = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
            ->where('status', 'available')
            ->whereNull('reseller_distributed_at')
            ->selectRaw('plan_id, COUNT(*) as qty')
            ->groupBy('plan_id')
            ->get()
            ->keyBy('plan_id');

        $plans = VoucherPlan::active()->get();

        // My stats
        $myTotalSold = WifiCode::where('reseller_staff_id', $staff->id)
            ->whereNotNull('reseller_distributed_at')
            ->count();

        $myMonthlySold = WifiCode::where('reseller_staff_id', $staff->id)
            ->whereNotNull('reseller_distributed_at')
            ->whereYear('reseller_distributed_at', now()->year)
            ->whereMonth('reseller_distributed_at', now()->month)
            ->count();

        $myTotalAoa = (int) DB::table('wifi_codes')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->where('wifi_codes.reseller_staff_id', $staff->id)
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->sum('voucher_plans.price_public_aoa');

        // Recent sales (last 30)
        $recentSales = WifiCode::where('reseller_staff_id', $staff->id)
            ->whereNotNull('reseller_distributed_at')
            ->orderByDesc('reseller_distributed_at')
            ->limit(30)
            ->get();

        return view('staff.panel', compact(
            'staff', 'application', 'plans', 'availableByPlan',
            'myTotalSold', 'myMonthlySold', 'myTotalAoa', 'recentSales'
        ));
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'pin'   => 'required|string|max:10',
        ]);

        $phone = preg_replace('/\D/', '', $validated['phone']);
        if (strlen($phone) > 9 && str_starts_with($phone, '244')) {
            $phone = substr($phone, 3);
        }

        $staff = ResellerStaff::whereRaw(
                "REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+','') LIKE ?",
                ['%' . $phone . '%']
            )
            ->where('status', ResellerStaff::STATUS_ACTIVE)
            ->first();

        if (!$staff || !$staff->checkPin($validated['pin'])) {
            return back()
                ->withErrors(['pin' => 'Telefone ou PIN incorrectos. Contacte o seu Agente Revendedor.'])
                ->withInput(['phone' => $request->phone]);
        }

        $request->session()->put(self::SESS, $staff->id);
        return redirect()->route('staff.panel');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(self::SESS);
        return redirect()->route('staff.panel');
    }

    public function sell(Request $request)
    {
        $staff = $this->getStaff($request);
        if (!$staff || !$staff->isActive()) {
            return redirect()->route('staff.panel');
        }

        $validated = $request->validate([
            'plan_slug'    => 'required|string|exists:voucher_plans,slug',
            'customer_ref' => 'nullable|string|max:255',
        ]);

        $application = $staff->application;
        $purchaseIds = $application->purchases()->pluck('id');

        $code = DB::transaction(function () use ($purchaseIds, $validated, $staff) {
            $code = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
                ->where('plan_id', $validated['plan_slug'])
                ->where('status', 'available')
                ->whereNull('reseller_distributed_at')
                ->lockForUpdate()
                ->orderBy('id')
                ->first();

            if (!$code) return null;

            $code->update([
                'reseller_distributed_at' => now(),
                'reseller_staff_id'       => $staff->id,
                'reseller_customer_ref'   => $validated['customer_ref'] ?? null,
            ]);

            return $code;
        });

        if (!$code) {
            return back()->with('sell_error', 'Sem stock disponível para este plano. Contacte o seu Agente Revendedor.');
        }

        return back()->with('sold_code', $code->code)->with('sold_plan', $validated['plan_slug']);
    }
}
