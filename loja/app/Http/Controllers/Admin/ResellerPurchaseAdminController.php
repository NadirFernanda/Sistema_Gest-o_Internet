<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerPurchase;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResellerPurchaseAdminController extends Controller
{
    public function approve(ResellerPurchase $purchase)
    {
        if ($purchase->status !== 'pending_confirmation') {
            return back()->with('error', "Compra #{$purchase->id} não está pendente de confirmação (estado: {$purchase->status}).");
        }

        $planMap = VoucherPlan::where('slug', $purchase->plan_slug)->get()->keyBy('slug');

        try {
            DB::transaction(function () use ($purchase, $planMap) {
                $p = ResellerPurchase::where('id', $purchase->id)
                    ->where('status', 'pending_confirmation')
                    ->lockForUpdate()
                    ->firstOrFail();

                $codes = WifiCode::where('plan_id', $p->plan_slug)
                    ->where('status', WifiCode::STATUS_AVAILABLE)
                    ->lockForUpdate()
                    ->limit($p->quantity)
                    ->get();

                if ($codes->count() < $p->quantity) {
                    throw new \RuntimeException("Stock insuficiente para \"{$p->plan_name}\": necessário {$p->quantity}, disponível {$codes->count()}.");
                }

                $validityLabel = optional($planMap->get($p->plan_slug))->validity_label ?? $p->plan_slug;
                $codeLines = ['plano,codigo,validade'];
                foreach ($codes as $wc) {
                    $codeLines[] = "{$p->plan_name},{$wc->code},{$validityLabel}";
                }
                Storage::disk('local')->put($p->csv_path, implode("\n", $codeLines) . "\n");

                WifiCode::whereIn('id', $codes->pluck('id'))->update([
                    'status'               => WifiCode::STATUS_USED,
                    'used_at'              => now(),
                    'reseller_purchase_id' => $p->id,
                ]);

                $p->update(['status' => 'completed']);

                Log::info("Admin aprovou compra de revendedor #{$p->id}", [
                    'reseller_application_id' => $p->reseller_application_id,
                    'plan'                    => $p->plan_slug,
                    'quantity'                => $p->quantity,
                    'payment_method'          => $p->payment_method,
                    'payment_reference'       => $p->payment_reference,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao aprovar compra: ' . $e->getMessage());
        }

        return back()->with('status', "✅ Compra #{$purchase->id} aprovada — {$purchase->quantity} voucher(s) entregues ao revendedor.");
    }

    public function reject(ResellerPurchase $purchase)
    {
        if ($purchase->status !== 'pending_confirmation') {
            return back()->with('error', "Compra #{$purchase->id} não está pendente de confirmação (estado: {$purchase->status}).");
        }

        $purchase->update(['status' => 'cancelled']);

        Log::info("Admin rejeitou compra de revendedor #{$purchase->id}", [
            'reseller_application_id' => $purchase->reseller_application_id,
            'plan'                    => $purchase->plan_slug,
            'payment_method'          => $purchase->payment_method,
            'payment_reference'       => $purchase->payment_reference,
        ]);

        return back()->with('status', "❌ Compra #{$purchase->id} rejeitada e marcada como cancelada.");
    }

    public function index(Request $request)
    {
        $query = ResellerPurchase::with('application')
            ->orderByRaw("CASE WHEN status = 'pending_confirmation' THEN 0 ELSE 1 END")
            ->orderByDesc('id');

        if ($resellerId = $request->get('reseller_id')) {
            $query->where('reseller_application_id', $resellerId);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $purchases = $query->paginate(25)->withQueryString();

        $pendingCount   = ResellerPurchase::where('status', 'pending_confirmation')->count();
        $totalRevenue   = ResellerPurchase::where('status', 'completed')->sum('net_amount_aoa');
        $totalCodes     = ResellerPurchase::where('status', 'completed')->sum('codes_count');
        $totalResellers = ResellerPurchase::where('status', 'completed')->distinct('reseller_application_id')->count('reseller_application_id');

        $ranking = ResellerPurchase::with('application')
            ->select(
                'reseller_application_id',
                DB::raw('SUM(net_amount_aoa)   as total_net'),
                DB::raw('SUM(gross_amount_aoa) as total_gross'),
                DB::raw('SUM(codes_count)      as total_codes'),
                DB::raw('COUNT(*)              as purchases_count')
            )
            ->groupBy('reseller_application_id')
            ->orderByDesc('total_net')
            ->get();

        return view('admin.resellers.purchases', compact(
            'purchases', 'totalRevenue', 'totalCodes', 'totalResellers', 'ranking', 'pendingCount'
        ));
    }
}
