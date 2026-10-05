<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

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

    public const FREQUENCY_PERIODS = [
        'day' => 'Dia',
        'week' => 'Semana',
        'month' => 'Mês',
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
        'frequency_period',
        'frequency_limit',
        'bonus_plan_slug',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'frequency_limit' => 'integer',
        ];
    }

    public function scopeRunning(Builder $query, string $placement): Builder
    {
        $query->where('placement', $placement)
            ->where('active', true)
            ->where(fn (Builder $campaign) => $campaign->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $campaign) => $campaign->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
        return $query;
    }

    public function scopeAvailable(Builder $query, string $placement): Builder
    {
        $query->running($placement);

        foreach (self::FREQUENCY_PERIODS as $period => $label) {
            $periodStart = self::periodStart($period);

            $query->where(function (Builder $campaign) use ($period, $periodStart) {
                $campaign->whereNull('frequency_limit')
                    ->orWhere('frequency_period', '!=', $period)
                    ->orWhereNotExists(function ($counts) use ($period, $periodStart) {
                        $counts->selectRaw('1')
                            ->from('ad_campaign_period_impressions')
                            ->whereColumn('ad_campaign_period_impressions.ad_campaign_id', 'ad_campaigns.id')
                            ->where('ad_campaign_period_impressions.frequency_period', $period)
                            ->where('ad_campaign_period_impressions.period_start', $periodStart)
                            ->whereColumn('ad_campaign_period_impressions.impressions_count', '>=', 'ad_campaigns.frequency_limit');
                    });
            });
        }

        return $query;
    }

    public static function periodStart(string $period): Carbon
    {
        return match ($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(Carbon::MONDAY),
            'month' => now()->startOfMonth(),
            default => throw new \InvalidArgumentException('Período de frequência inválido.'),
        };
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
