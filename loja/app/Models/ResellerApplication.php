<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
        'meta',
    ];

    protected $casts = [
        'notified_at'          => 'datetime',
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

    /** Returns the discount % to apply on a purchase of $grossAoa for this reseller. */
    public function discountPercentFor(int $grossAoa): int
    {
        if ($this->reseller_mode === self::INTERNET_OWN) {
            return (int) config('reseller.mode_own_discount_percent', 70);
        }

        // Modo 2: escalões por volume
        $tiers = config('reseller.mode_angolawifi_discount_tiers', []);
        ksort($tiers);
        $discount = 0;
        foreach ($tiers as $min => $percent) {
            if ($grossAoa >= $min) {
                $discount = $percent;
            }
        }
        return $discount;
    }

    /** Total purchased this calendar month (gross). */
    public function monthlySpendings(): int
    {
        return (int) $this->purchases()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('gross_amount_aoa');
    }

    /** True if the reseller met their monthly target this month. */
    public function metMonthlyTarget(): bool
    {
        if ($this->monthly_target_aoa <= 0) return true;
        return $this->monthlySpendings() >= $this->monthly_target_aoa;
    }

    /** True if the monthly maintenance fee has not been paid for the current month. */
    public function maintenanceDueThisMonth(): bool
    {
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
