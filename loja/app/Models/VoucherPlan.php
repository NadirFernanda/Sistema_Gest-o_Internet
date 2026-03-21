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

    /** Lucro por voucher para o revendedor */
    public function profitPerVoucher(): int
    {
        return $this->price_public_aoa - $this->price_reseller_aoa;
    }

    /** Margem em percentagem */
    public function marginPercent(): float
    {
        if ($this->price_public_aoa === 0) return 0;
        return round(($this->profitPerVoucher() / $this->price_public_aoa) * 100, 1);
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
