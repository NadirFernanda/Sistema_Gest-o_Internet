<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoucherPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'validity_label', 'validity_minutes',
        'speed_label', 'price_public_aoa', 'price_reseller_aoa',
        'active', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price_public_aoa'   => 'integer',
        'price_reseller_aoa' => 'integer',
        'validity_minutes'   => 'integer',
        'sort_order'         => 'integer',
    ];

    public function resellerPurchases(): HasMany
    {
        return $this->hasMany(ResellerPurchase::class, 'voucher_plan_id');
    }

    /** Preço efectivo para um revendedor específico, considerando o modo (own/angolawifi) */
    public function resellerPriceFor(?\App\Models\ResellerApplication $application): int
    {
        if (!$application) return $this->price_reseller_aoa;

        if ($application->reseller_mode === 'own') {
            $discount = config('reseller.mode_own_discount_percent', 70);
            return (int) round($this->price_public_aoa * (1 - $discount / 100));
        }

        // angolawifi: escalão baseado no gasto mensal acumulado
        $tiers        = config('reseller.mode_angolawifi_discount_tiers', []);
        $monthlySpend = $application->monthlySpendings();
        $discount = 0;
        foreach ($tiers as $min => $pct) {
            if ($monthlySpend >= $min) $discount = $pct;
        }
        return (int) round($this->price_public_aoa * (1 - $discount / 100));
    }

    /** Lucro por voucher para o revendedor (modo-aware) */
    public function profitPerVoucher(): int
    {
        return $this->price_public_aoa - $this->price_reseller_aoa;
    }

    /** Lucro por voucher para um revendedor específico */
    public function profitForReseller(?\App\Models\ResellerApplication $application): int
    {
        return $this->price_public_aoa - $this->resellerPriceFor($application);
    }

    /** Margem em percentagem (estática, sobre price_reseller_aoa) */
    public function marginPercent(): float
    {
        if ($this->price_public_aoa === 0) return 0;
        return round(($this->profitPerVoucher() / $this->price_public_aoa) * 100, 1);
    }

    /** Margem em percentagem para um revendedor específico */
    public function marginPercentForReseller(?\App\Models\ResellerApplication $application): float
    {
        if ($this->price_public_aoa === 0) return 0;
        return round(($this->profitForReseller($application) / $this->price_public_aoa) * 100, 1);
    }

    /** Conta vouchers disponíveis em stock */
    public function availableStock(): int
    {
        return \App\Models\WifiCode::where('plan_id', $this->slug)
            ->where('status', 'available')
            ->count();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('sort_order');
    }
}
