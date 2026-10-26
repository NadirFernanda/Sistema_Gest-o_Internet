<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use App\Models\VoucherPlan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PublicWifiAdController extends Controller
{
    private const SOL_TRACKING_URL_TTL_DAYS = 30;

    public function index(): Response
    {
        $campaigns = AdCampaign::available(AdCampaign::SOL_PORTAL_PLACEMENT)
            ->with('bonusPlan')
            ->get()
            ->filter(function (AdCampaign $candidate): bool {
                return Storage::disk('public')->exists($candidate->image_path)
                    && ($candidate->campaign_type !== 'own'
                        || ! $candidate->bonus_plan_slug
                        || ($candidate->bonusPlan?->active
                        && \App\Models\WifiCode::where('plan_id', $candidate->bonus_plan_slug)
                            ->where('status', \App\Models\WifiCode::STATUS_AVAILABLE)
                            ->whereNull('reseller_purchase_id')
                            ->whereNull('autovenda_order_id')
                            ->exists()));
            });
        $campaign = $campaigns->isEmpty() ? null : $campaigns->random();
        $impressionNonce = $campaign ? bin2hex(random_bytes(32)) : null;
        $clickNonce = $campaign ? bin2hex(random_bytes(32)) : null;

        return response()->view('store.portal-wifi-ad', [
            'campaign' => $campaign,
            'impressionUrl' => $campaign
                ? URL::signedRoute(
                    'ads.sol-impression',
                    ['campaign' => $campaign->id, 'nonce' => $impressionNonce],
                    now()->addDays(self::SOL_TRACKING_URL_TTL_DAYS),
                    false
                )
                : null,
            'clickUrl' => $campaign
                ? URL::signedRoute(
                    'ads.sol-click',
                    ['campaign' => $campaign->id, 'nonce' => $clickNonce],
                    now()->addDays(self::SOL_TRACKING_URL_TTL_DAYS),
                    false
                )
                : null,
            'plans' => VoucherPlan::active()->get([
                'slug',
                'name',
                'validity_label',
                'speed_label',
                'price_public_aoa',
            ]),
        ])->header('Cache-Control', 'no-store, private');
    }
}
