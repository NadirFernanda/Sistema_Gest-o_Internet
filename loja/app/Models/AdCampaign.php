<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdCampaign extends Model
{
    public const TYPES = [
        'own' => 'Campanha própria AngolaWiFi',
        'sponsored' => 'Publicidade de anunciante',
    ];

    public const PLACEMENTS = [
        'home_banner' => 'Página inicial — abaixo do destaque',
        'equipment_list' => 'Catálogo de equipamentos — acima dos produtos',
    ];

    protected $fillable = [
        'campaign_type',
        'advertiser_name',
        'title',
        'description',
        'image_path',
        'destination_url',
        'button_text',
        'placement',
        'active',
        'starts_at',
        'ends_at',
        'bonus_plan_slug',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeAvailable(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement)
            ->where('active', true)
            ->where(fn (Builder $campaign) => $campaign->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $campaign) => $campaign->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function qualifyingPlans(): BelongsToMany
    {
        return $this->belongsToMany(
            VoucherPlan::class,
            'ad_campaign_qualifying_plans',
            'ad_campaign_id',
            'purchase_plan_slug',
            'id',
            'slug'
        );
    }

    public function bonusPlan(): BelongsTo
    {
        return $this->belongsTo(VoucherPlan::class, 'bonus_plan_slug', 'slug');
    }

    public static function bonusForPurchasePlan(string $planSlug): ?self
    {
        $campaigns = self::query()
            ->where('campaign_type', 'own')
            ->whereNotNull('bonus_plan_slug')
            ->where('active', true)
            ->where(fn (Builder $campaign) => $campaign->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $campaign) => $campaign->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->whereHas('qualifyingPlans', fn (Builder $plans) => $plans->where('voucher_plans.slug', $planSlug))
            ->whereHas('bonusPlan', fn (Builder $plan) => $plan->where('active', true))
            ->with('bonusPlan')
            ->orderByDesc('id')
            ->get();

        foreach ($campaigns as $campaign) {
            if (\App\Models\WifiCode::where('plan_id', $campaign->bonus_plan_slug)
                ->where('status', WifiCode::STATUS_AVAILABLE)
                ->whereNull('reseller_purchase_id')
                ->whereNull('autovenda_order_id')
                ->exists()) {
                return $campaign;
            }
        }

        return null;
    }
}
