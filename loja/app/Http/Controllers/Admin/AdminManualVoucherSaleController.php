<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerApplication;
use App\Models\ResellerPurchase;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminManualVoucherSaleController extends Controller
{
    /**
     * Formulário de venda manual de vouchers a um revendedor.
     */
    public function create()
    {
        $resellers    = ResellerApplication::where('status', 'approved')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone', 'email']);

        $voucherPlans = VoucherPlan::active()
            ->orderBy('sort_order')
            ->get();

        return view('admin.resellers.manual_sale', compact('resellers', 'voucherPlans'));
    }

    /**
     * Processa a venda manual e atribui os vouchers directamente ao painel do revendedor.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reseller_id'  => ['required', 'integer', 'exists:reseller_applications,id'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.plan' => ['required', 'string', 'exists:voucher_plans,slug'],
            'items.*.qty'  => ['required', 'integer', 'min:1', 'max:9999'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $application = ResellerApplication::where('id', $data['reseller_id'])
            ->where('status', 'approved')
            ->firstOrFail();

        // Filtrar itens com planos únicos (remover duplicados por slug)
        $items = collect($data['items'])
            ->groupBy('plan')
            ->map(fn($group, $slug) => [
                'plan' => $slug,
                'qty'  => $group->sum(fn($i) => (int) $i['qty']),
            ])
            ->values()
            ->filter(fn($i) => $i['qty'] > 0);

        if ($items->isEmpty()) {
            return back()->with('error', 'Nenhum item válido para processar.');
        }

        $voucherPlans = VoucherPlan::active()
            ->whereIn('slug', $items->pluck('plan'))
            ->get()
            ->keyBy('slug');

        // Verificar stock antecipadamente
        foreach ($items as $item) {
            $plan  = $voucherPlans->get($item['plan']);
            if (!$plan) {
                return back()->with('error', "Plano \"{$item['plan']}\" não encontrado ou inactivo.");
            }
            $stock = WifiCode::where('plan_id', $item['plan'])
                ->where('status', 'available')
                ->count();
            if ($stock < $item['qty']) {
                return back()->with('error',
                    "Stock insuficiente para \"{$plan->name}\": pediu {$item['qty']}, disponível {$stock}."
                );
            }
        }

        $purchaseIds = [];
        $notes       = trim($data['notes'] ?? '');

        try {
            DB::transaction(function () use ($items, $voucherPlans, $application, $notes, &$purchaseIds) {
                foreach ($items as $item) {
                    $plan = $voucherPlans->get($item['plan']);
                    $qty  = (int) $item['qty'];

                    // Bloquear e obter os códigos disponíveis
                    $codes = WifiCode::where('plan_id', $item['plan'])
                        ->where('status', 'available')
                        ->lockForUpdate()
                        ->limit($qty)
                        ->get();

                    if ($codes->count() < $qty) {
                        throw new \RuntimeException(
                            "Stock insuficiente para {$plan->name} no momento da confirmação."
                        );
                    }

                    // Calcular valores (mesmo esquema do checkout normal)
                    $unitPrice   = $plan->resellerPriceFor($application);
                    $grossAmt    = $plan->price_public_aoa * $qty;
                    $resellerCost = $unitPrice * $qty;
                    $grossProfit = $grossAmt - $resellerCost;
                    $taxAoa      = (int) round($grossProfit * 0.065);
                    $netProfit   = $grossProfit - $taxAoa;
                    $netAmount   = $resellerCost + $taxAoa;
                    $discPct     = $plan->price_public_aoa > 0
                        ? round((1 - $unitPrice / $plan->price_public_aoa) * 100, 1)
                        : 0;

                    $path = 'resellers/' . $application->id
                        . '/purchase_' . $item['plan']
                        . '_' . now()->format('Ymd_His')
                        . '_' . Str::random(6) . '.csv';

                    // Gerar CSV dos códigos
                    $codeLines = ['plano,codigo,validade'];
                    foreach ($codes as $wc) {
                        $codeLines[] = "{$plan->name},{$wc->code},{$plan->validity_label}";
                    }
                    Storage::disk('local')->put($path, implode("\n", $codeLines) . "\n");

                    $purchase = ResellerPurchase::create([
                        'reseller_application_id' => $application->id,
                        'voucher_plan_id'          => $plan->id,
                        'plan_slug'                => $item['plan'],
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
                        'status'                   => 'completed',
                        'payment_method'           => 'manual_admin',
                        'payment_reference'        => 'ADMIN-MANUAL-' . now()->format('YmdHis'),
                        'paid_at'                  => now(),
                        'meta'                     => [
                            'manual_sale'   => true,
                            'admin_notes'   => $notes,
                            'code_preview'  => $codes->take(3)->pluck('code')->toArray(),
                        ],
                    ]);

                    // Marcar códigos como usados e associados a esta compra
                    WifiCode::whereIn('id', $codes->pluck('id'))->update([
                        'status'               => 'used',
                        'used_at'              => now(),
                        'reseller_purchase_id' => $purchase->id,
                    ]);

                    $purchaseIds[] = $purchase->id;
                }
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao processar a venda: ' . $e->getMessage());
        }

        $totalVouchers = ResellerPurchase::whereIn('id', $purchaseIds)->sum('codes_count');

        return redirect()->route('admin.manual_voucher_sale.create')
            ->with('success',
                "{$totalVouchers} voucher(s) enviados com sucesso para o painel de \"{$application->full_name}\"."
            );
    }
}
