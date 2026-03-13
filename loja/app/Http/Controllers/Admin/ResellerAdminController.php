<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerApplication;
use Illuminate\Http\Request;

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
        $purchases     = $application->purchases()->orderByDesc('id')->paginate(10);
        $monthlySpend  = $application->monthlySpendings();
        $totalRevenue  = $application->purchases()->sum('net_amount_aoa');
        $totalProfit   = $application->purchases()->selectRaw('SUM(gross_amount_aoa - net_amount_aoa) as profit')->value('profit') ?? 0;

        return view('admin.resellers.show', compact(
            'application', 'purchases', 'monthlySpend', 'totalRevenue', 'totalProfit'
        ));
    }

    public function update(Request $request, ResellerApplication $application)
    {
        $data = $request->validate([
            'reseller_mode'        => 'nullable|in:own,angolawifi',
            'installation_fee_aoa' => 'nullable|integer|min:0',
            'monthly_target_aoa'   => 'nullable|integer|min:0',
            'maintenance_paid_year'=> 'nullable|integer|min:2020|max:2100',
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
        $request->validate(['status' => 'required|in:pending,approved,rejected']);
        $application->update(['status' => $request->status]);

        return back()->with('status', 'Estado atualizado.');
    }
}
