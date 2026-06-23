<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ResellerApplication extends Model
{
    protected $fillable = [
        'full_name',
        'document_number',
        'address',
        'email',
        'phone',
        'installation_location',
        'internet_type',
        'reseller_mode',
        'installation_fee_aoa',
        'bonus_vouchers_aoa',
        'saldo_bonus_aoa',
        'monthly_target_aoa',
        'maintenance_paid_year',
        'maintenance_paid_month',
        'maintenance_status',
        'notes',
        'subject',
        'message',
        'status',
        'notified_at',
        'approved_at',
        'meta',
    ];

    protected $casts = [
        'notified_at'          => 'datetime',
        'approved_at'          => 'datetime',
        'meta'                 => 'array',
        'installation_fee_aoa' => 'integer',
        'bonus_vouchers_aoa'   => 'integer',
        'saldo_bonus_aoa'      => 'integer',
        'monthly_target_aoa'   => 'integer',
        'maintenance_paid_year' => 'integer',
        'maintenance_paid_month'=> 'integer',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** Revendedor tem internet própria (Modo 1) — desconto fixo de 70%. */
    public const INTERNET_OWN        = 'own';
    /** Revendedor depende de internet AngolaWiFi (Modo 2) — desconto escalonado. */
    public const INTERNET_ANGOLAWIFI = 'angolawifi';

    public const MAINTENANCE_OK      = 'ok';
    public const MAINTENANCE_PENDING = 'pending';
    public const MAINTENANCE_OVERDUE = 'overdue';

    // ─── Relationships ────────────────────────────────────────────────────────

    public function purchases()
    {
        return $this->hasMany(ResellerPurchase::class, 'reseller_application_id');
    }

    public function bonusTransactions()
    {
        return $this->hasMany(ResellerBonusTransaction::class, 'reseller_application_id');
    }

    // ─── Business logic helpers ───────────────────────────────────────────────

    /** Returns the discount % for this reseller based on their mode. */
    public function discountPercentFor(int $grossAoa = 0): int
    {
        if ($this->reseller_mode === self::INTERNET_OWN) {
            return (int) config('reseller.mode_own_discount_percent', 70);
        }

        return (int) config('reseller.mode_angolawifi_discount_percent', 30);
    }

    /** Total purchased this calendar month (gross). Kept for admin stats. */
    public function monthlySpendings(): int
    {
        return (int) $this->purchases()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('gross_amount_aoa');
    }

    /** Total sold to end-customers this calendar month (sum of public prices of distributed codes). */
    public function monthlySales(): int
    {
        $purchaseIds = $this->purchases()->pluck('id');
        if ($purchaseIds->isEmpty()) return 0;

        $salesByPlan = WifiCode::whereIn('reseller_purchase_id', $purchaseIds)
            ->whereNotNull('reseller_distributed_at')
            ->whereYear('reseller_distributed_at', now()->year)
            ->whereMonth('reseller_distributed_at', now()->month)
            ->selectRaw('plan_id, COUNT(*) as qty')
            ->groupBy('plan_id')
            ->get();

        if ($salesByPlan->isEmpty()) return 0;

        $plans = VoucherPlan::whereIn('slug', $salesByPlan->pluck('plan_id'))->get()->keyBy('slug');

        return (int) $salesByPlan->sum(
            fn($row) => $row->qty * ($plans[$row->plan_id]->price_public_aoa ?? 0)
        );
    }

    /** Top N resellers ranked by voucher sales value this month. */
    public static function topSellersThisMonth(int $limit = 10): \Illuminate\Support\Collection
    {
        return DB::table('wifi_codes')
            ->join('reseller_purchases', 'wifi_codes.reseller_purchase_id', '=', 'reseller_purchases.id')
            ->join('reseller_applications', 'reseller_purchases.reseller_application_id', '=', 'reseller_applications.id')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->whereYear('wifi_codes.reseller_distributed_at', now()->year)
            ->whereMonth('wifi_codes.reseller_distributed_at', now()->month)
            ->select(
                'reseller_applications.id as reseller_id',
                'reseller_applications.full_name',
                DB::raw('SUM(voucher_plans.price_public_aoa) as total_sales_aoa'),
                DB::raw('COUNT(wifi_codes.id) as vouchers_sold')
            )
            ->groupBy('reseller_applications.id', 'reseller_applications.full_name')
            ->orderByDesc('total_sales_aoa')
            ->limit($limit)
            ->get();
    }

    /** True if the reseller met their monthly target this month (based on sales to customers). */
    public function metMonthlyTarget(): bool
    {
        if ($this->monthly_target_aoa <= 0) return true;
        return $this->monthlySales() >= $this->monthly_target_aoa;
    }

    /** True if the monthly maintenance fee has not been paid for the current month. */
    public function maintenanceDueThisMonth(): bool
    {
        // Período de graça: no mês de aprovação a taxa não é cobrada
        if ($this->approved_at !== null
            && $this->approved_at->year  === now()->year
            && $this->approved_at->month === now()->month) {
            return false;
        }

        return !(($this->maintenance_paid_year  ?? 0) === now()->year
              && ($this->maintenance_paid_month ?? 0) === now()->month);
    }

    /** Maintenance fee amount for this reseller's mode. */
    public function maintenanceFeeAoa(): int
    {
        return $this->reseller_mode === self::INTERNET_OWN
            ? (int) config('reseller.mode_own_maintenance_aoa', 50000)
            : (int) config('reseller.mode_angolawifi_maintenance_aoa', 100000);
    }

    /**
     * Allocate voucher codes as maintenance benefit.
     * Budget = maintenanceFeeAoa(); qty per plan is calculated using the AR's
     * actual cost price (resellerPriceFor), which already encodes the 70/30 or 30/70 margin.
     * Returns an array of ['plan', 'qty', 'purchase_id'] entries.
     */
    public function allocateMaintenanceVouchers(int $year, int $month): array
    {
        $budget = $this->maintenanceFeeAoa();
        if ($budget <= 0) return [];

        $bonusRef = 'BONUS-MNT-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

        if (ResellerPurchase::where('reseller_application_id', $this->id)
                ->where('payment_reference', $bonusRef)->exists()) {
            return [];
        }

        $plans = VoucherPlan::active()->orderBy('sort_order')->get();
        if ($plans->isEmpty()) return [];

        $perPlanBudget = (int) floor($budget / $plans->count());
        $allocated     = [];

        try {
            DB::transaction(function () use ($plans, $perPlanBudget, $year, $month, $bonusRef, &$allocated) {
                foreach ($plans as $plan) {
                    $costPrice = $plan->resellerPriceFor($this);
                    if ($costPrice <= 0) continue;

                    $qty = max(1, (int) floor($perPlanBudget / $costPrice));

                    $codes = WifiCode::where('plan_id', $plan->slug)
                        ->where('status', WifiCode::STATUS_AVAILABLE)
                        ->whereNull('reseller_purchase_id')
                        ->lockForUpdate()
                        ->limit($qty)
                        ->get();

                    if ($codes->isEmpty()) continue;

                    $actualQty = $codes->count();
                    $path      = 'resellers/' . $this->id
                                 . '/manutencao_' . $plan->slug . '_' . now()->format('Ymd_His') . '.csv';

                    $purchase = ResellerPurchase::create([
                        'reseller_application_id' => $this->id,
                        'voucher_plan_id'          => $plan->id,
                        'plan_slug'                => $plan->slug,
                        'plan_name'                => $plan->name,
                        'quantity'                 => $actualQty,
                        'unit_price_aoa'           => $costPrice,
                        'gross_amount_aoa'         => $plan->price_public_aoa * $actualQty,
                        'discount_percent'         => $this->discountPercentFor(),
                        'net_amount_aoa'           => $costPrice * $actualQty,
                        'codes_count'              => $actualQty,
                        'profit_aoa'               => ($plan->price_public_aoa - $costPrice) * $actualQty,
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
                        'status'               => WifiCode::STATUS_USED,
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
            Log::error('Manutenção: erro ao alocar vouchers', [
                'reseller_id' => $this->id,
                'period'      => "$year/$month",
                'error'       => $e->getMessage(),
            ]);
        }

        return $allocated;
    }

    /** Mark installation fee paid and generate bonus vouchers credit. */
    public function applyInstallationFee(int $feeAoa): void
    {
        $bonusPercent = (int) config('reseller.bonus_install_percent', 50);
        $bonus = (int) round($feeAoa * $bonusPercent / 100);
        $monthlyTarget = ($this->reseller_mode === self::INTERNET_OWN)
            ? (int) round($feeAoa * config('reseller.monthly_target_percent', 50) / 100)
            : 0;

        $this->update([
            'installation_fee_aoa' => $feeAoa,
            'bonus_vouchers_aoa'   => $bonus,
            'monthly_target_aoa'   => $monthlyTarget,
        ]);
    }
}
