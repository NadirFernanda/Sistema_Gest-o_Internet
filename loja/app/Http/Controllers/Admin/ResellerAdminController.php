<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerApplication;
use App\Mail\ResellerStatusMail;
use App\Models\ResellerBonusTransaction;
use App\Models\ResellerPurchase;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResellerAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ResellerApplication::query()->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        $statusCounts = ResellerApplication::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.resellers.index', [
            'applications' => $applications,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(ResellerApplication $application)
    {
        $purchases         = $application->purchases()->orderByDesc('id')->paginate(15);
        $monthlySpend      = $application->monthlySales();
        $totalRevenue      = $application->purchases()->sum('net_amount_aoa');
        $totalProfit       = $application->purchases()->selectRaw('SUM(gross_amount_aoa - net_amount_aoa) as profit')->value('profit') ?? 0;
        $bonusTransactions = $application->bonusTransactions()->orderByDesc('id')->limit(20)->get();

        // Sales stats (codes distributed to customers)
        $allPurchaseIds = $application->purchases()->pluck('id');
        $salesStats = DB::table('wifi_codes')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->whereIn('wifi_codes.reseller_purchase_id', $allPurchaseIds)
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->selectRaw('COUNT(*) as sold_count, COALESCE(SUM(voucher_plans.price_public_aoa), 0) as sales_aoa')
            ->first();

        $totalVouchersBought = $application->purchases()->sum('codes_count');
        $totalVouchersSold   = $salesStats->sold_count ?? 0;
        $totalSalesAoa       = $salesStats->sales_aoa  ?? 0;
        $stockRemaining      = $totalVouchersBought - $totalVouchersSold;

        // Sold count per purchase (current page only — for the table)
        $soldPerPurchase = DB::table('wifi_codes')
            ->whereIn('reseller_purchase_id', $purchases->pluck('id'))
            ->whereNotNull('reseller_distributed_at')
            ->selectRaw('reseller_purchase_id, COUNT(*) as sold')
            ->groupBy('reseller_purchase_id')
            ->pluck('sold', 'reseller_purchase_id');

        // Monthly breakdown: compras vs vendas (last 12 months)
        $monthlyCompras = $application->purchases()
            ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, SUM(net_amount_aoa) as total, SUM(codes_count) as codes")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthlyVendas = DB::table('wifi_codes')
            ->join('reseller_purchases', 'wifi_codes.reseller_purchase_id', '=', 'reseller_purchases.id')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->whereIn('wifi_codes.reseller_purchase_id', $allPurchaseIds)
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->where('wifi_codes.reseller_distributed_at', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("TO_CHAR(wifi_codes.reseller_distributed_at, 'YYYY-MM') as month, COUNT(*) as codes, COALESCE(SUM(voucher_plans.price_public_aoa), 0) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        return view('admin.resellers.show', compact(
            'application', 'purchases', 'monthlySpend', 'totalRevenue', 'totalProfit',
            'bonusTransactions', 'totalVouchersBought', 'totalVouchersSold', 'totalSalesAoa',
            'stockRemaining', 'soldPerPurchase', 'monthlyCompras', 'monthlyVendas'
        ));
    }

    public function historyOverview()
    {
        $resellers = ResellerApplication::where('status', 'approved')
            ->withCount('purchases as total_purchases')
            ->withSum('purchases as total_spent', 'net_amount_aoa')
            ->withSum('purchases as total_bought_codes', 'codes_count')
            ->orderBy('full_name')
            ->get();

        if ($resellers->isEmpty()) {
            return view('admin.resellers.history', [
                'stats' => collect(),
                'globalTotals' => ['spent' => 0, 'sales' => 0, 'bought' => 0, 'sold' => 0, 'stock' => 0],
            ]);
        }

        // Sold stats per reseller in one query
        $soldStats = DB::table('wifi_codes')
            ->join('reseller_purchases', 'wifi_codes.reseller_purchase_id', '=', 'reseller_purchases.id')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->whereIn('reseller_purchases.reseller_application_id', $resellers->pluck('id'))
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->selectRaw('reseller_purchases.reseller_application_id, COUNT(*) as sold_count, COALESCE(SUM(voucher_plans.price_public_aoa), 0) as sales_aoa, MAX(wifi_codes.reseller_distributed_at) as last_sale')
            ->groupBy('reseller_purchases.reseller_application_id')
            ->get()
            ->keyBy('reseller_application_id');

        $lastPurchase = ResellerPurchase::whereIn('reseller_application_id', $resellers->pluck('id'))
            ->selectRaw('reseller_application_id, MAX(created_at) as last_purchase')
            ->groupBy('reseller_application_id')
            ->pluck('last_purchase', 'reseller_application_id');

        $stats = $resellers->map(function ($app) use ($soldStats, $lastPurchase) {
            $sold      = $soldStats[$app->id] ?? null;
            $bought    = (int) ($app->total_bought_codes ?? 0);
            $soldCount = (int) ($sold->sold_count ?? 0);
            $salesAoa  = (int) ($sold->sales_aoa  ?? 0);
            $spent     = (int) ($app->total_spent ?? 0);

            return (object) [
                'application'    => $app,
                'total_purchases'=> $app->total_purchases,
                'total_bought'   => $bought,
                'total_spent'    => $spent,
                'total_sold'     => $soldCount,
                'total_sales_aoa'=> $salesAoa,
                'stock'          => $bought - $soldCount,
                'profit_estimate'=> $salesAoa - $spent,
                'last_purchase'  => $lastPurchase[$app->id] ?? null,
                'last_sale'      => $sold->last_sale ?? null,
            ];
        })->sortByDesc('total_spent');

        $globalTotals = [
            'spent'  => $stats->sum('total_spent'),
            'sales'  => $stats->sum('total_sales_aoa'),
            'bought' => $stats->sum('total_bought'),
            'sold'   => $stats->sum('total_sold'),
            'stock'  => $stats->sum('stock'),
        ];

        return view('admin.resellers.history', compact('stats', 'globalTotals'));
    }

    public function update(Request $request, ResellerApplication $application)
    {
        $data = $request->validate([
            'reseller_mode'        => 'nullable|in:own,angolawifi',
            'installation_fee_aoa' => 'nullable|integer|min:0',
            'monthly_target_aoa'   => 'nullable|integer|min:0',
            'maintenance_paid_year' => 'nullable|integer|min:2020|max:2100',
            'maintenance_paid_month'=> 'nullable|integer|min:1|max:12',
            'maintenance_status'   => 'nullable|in:ok,pending,overdue',
            'notes'                => 'nullable|string|max:2000',
        ]);

        // When installation fee is set, auto-compute bonus vouchers.
        if (isset($data['installation_fee_aoa']) && $data['installation_fee_aoa'] > 0) {
            $bonusPct = (int) config('reseller.bonus_install_percent', 50);
            $data['bonus_vouchers_aoa'] = (int) round($data['installation_fee_aoa'] * $bonusPct / 100);

            // Auto-set monthly target for Modo 1 if not explicitly given.
            if (($data['reseller_mode'] ?? $application->reseller_mode) === 'own'
                && empty($data['monthly_target_aoa'])) {
                $targetPct = (int) config('reseller.monthly_target_percent', 50);
                $data['monthly_target_aoa'] = (int) round($data['installation_fee_aoa'] * $targetPct / 100);
            }
        }

        $application->update($data);

        return redirect()->route('admin.resellers.show', $application)
            ->with('status', 'Dados do revendedor actualizados com sucesso.');
    }

    public function updateStatus(Request $request, ResellerApplication $application)
    {
        $request->validate([
            'status'           => 'required|in:pending,approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:1000',
        ], [
            'rejection_reason.required_if' => 'É obrigatório indicar o motivo da rejeição.',
        ]);

        $oldStatus  = $application->status;
        $newStatus  = $request->status;
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'approved' && $oldStatus !== 'approved') {
            $updateData['approved_at'] = now();
        }

        $application->update($updateData);

        if ($oldStatus !== $newStatus && in_array($newStatus, ['approved', 'rejected'])) {
            $reason = $newStatus === 'rejected' ? $request->rejection_reason : null;
            Mail::to($application->email)->send(new ResellerStatusMail($application, $reason));
        }

        $label = $newStatus === 'approved' ? 'aprovada — contrato enviado por e-mail' : 'rejeitada — candidato notificado';
        return back()->with('status', 'Candidatura ' . $label . '.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));

        if (empty($ids)) {
            return back()->with('error', 'Nenhuma candidatura seleccionada.');
        }

        // Aprovados com compras não são eliminados — podem ter histórico financeiro
        $deleted = ResellerApplication::whereIn('id', $ids)
            ->where(function ($q) {
                $q->where('status', '!=', ResellerApplication::STATUS_APPROVED)
                  ->orWhereDoesntHave('purchases');
            })
            ->delete();

        $skipped = count($ids) - $deleted;

        $msg = $deleted . ' candidatura(s) eliminada(s).';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' ignorada(s): revendedores aprovados com compras não podem ser eliminados aqui.';
        }

        return back()->with('success', $msg);
    }

    public function sendBonus(Request $request, ResellerApplication $application)
    {
        $data = $request->validate([
            'amount_aoa' => 'required|integer|min:1|max:100000000',
            'reason'     => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($application, $data) {
            ResellerBonusTransaction::create([
                'reseller_application_id' => $application->id,
                'amount_aoa'              => $data['amount_aoa'],
                'reason'                  => $data['reason'] ?? null,
            ]);

            $application->increment('saldo_bonus_aoa', $data['amount_aoa']);
        });

        return redirect()->route('admin.resellers.show', $application)
            ->with('status', 'Bónus de ' . number_format($data['amount_aoa'], 0, ',', '.') . ' Kz enviado com sucesso.');
    }

    public function payMaintenance(Request $request, ResellerApplication $application)
    {
        $data = $request->validate([
            'year'  => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year  = (int) $data['year'];
        $month = (int) $data['month'];

        // Idempotência: já registado para este período?
        if ((int) ($application->maintenance_paid_year ?? 0)  === $year
            && (int) ($application->maintenance_paid_month ?? 0) === $month) {
            return redirect()->route('admin.resellers.show', $application)
                ->with('status', 'Manutenção de ' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year . ' já estava registada.');
        }

        $application->update([
            'maintenance_paid_year'  => $year,
            'maintenance_paid_month' => $month,
            'maintenance_status'     => ResellerApplication::MAINTENANCE_OK,
        ]);

        $allocated = $application->allocateMaintenanceVouchers($year, $month);

        $fee   = number_format($application->maintenanceFeeAoa(), 0, ',', '.');
        $total = array_sum(array_column($allocated, 'qty'));
        $msg   = "Manutenção {$month}/{$year} registada ({$fee} Kz). Vouchers alocados: {$total} código(s).";

        return redirect()->route('admin.resellers.show', $application)->with('status', $msg);
    }
}
