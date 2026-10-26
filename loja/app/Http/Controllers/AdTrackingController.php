<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdTrackingController extends Controller
{
    private const SOL_TRACKING_DEDUPE_DAYS = 31;

    public function image(int $campaign): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->campaignImage($campaign);
    }

    public function solImage(int $campaign): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->campaignImage($campaign, AdCampaign::SOL_PORTAL_PLACEMENT);
    }

    private function campaignImage(
        int $campaign,
        ?string $placement = null
    ): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse {
        $query = AdCampaign::query();
        if ($placement !== null) {
            $query->where('placement', $placement);
        }

        $ad = $query->findOrFail($campaign);
        abort_unless(AdCampaign::running($ad->placement)->whereKey($ad->id)->exists(), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($ad->image_path), 404);

        return response()->file($disk->path($ad->image_path), [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function impression(Request $request, int $campaign): Response
    {
        $data = $request->validate([
            'placement' => ['required', 'string', 'in:' . implode(',', array_keys(AdCampaign::PLACEMENTS))],
        ]);

        DB::transaction(function () use ($request, $campaign, $data): void {
            $ad = AdCampaign::available($data['placement'])
                ->lockForUpdate()
                ->findOrFail($campaign);
            $sessionKey = 'ad_impression_' . $ad->id;
            $lastRecorded = $request->session()->get($sessionKey);

            if (is_numeric($lastRecorded) && (int) $lastRecorded >= now()->subMinutes(30)->timestamp) {
                return;
            }

            $this->incrementImpression($ad);
            $request->session()->put($sessionKey, now()->timestamp);
        });

        return response()->noContent();
    }

    public function solImpression(int $campaign, string $nonce): Response
    {
        $cacheKey = 'sol_ad_impression:' . hash('sha256', $nonce);
        if (Cache::store('sol_ads')->add($cacheKey, true, now()->addDays(self::SOL_TRACKING_DEDUPE_DAYS))) {
            try {
                DB::transaction(function () use ($campaign): void {
                    $ad = AdCampaign::available(AdCampaign::SOL_PORTAL_PLACEMENT)
                        ->lockForUpdate()
                        ->findOrFail($campaign);

                    $this->incrementImpression($ad);
                });
            } catch (\Throwable $exception) {
                Cache::store('sol_ads')->forget($cacheKey);
                throw $exception;
            }
        }

        return response(base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=', true), 200, [
            'Cache-Control' => 'no-store, private',
            'Content-Type' => 'image/gif',
        ]);
    }

    public function click(int $campaign): RedirectResponse
    {
        $ad = AdCampaign::query()->findOrFail($campaign);
        abort_unless(AdCampaign::running($ad->placement)->whereKey($ad->id)->exists(), 404);

        $ad->increment('clicks_count');

        return redirect()->away($ad->destination_url);
    }

    public function solClick(int $campaign, string $nonce): RedirectResponse
    {
        $ad = AdCampaign::running(AdCampaign::SOL_PORTAL_PLACEMENT)->findOrFail($campaign);
        $cacheKey = 'sol_ad_click:' . hash('sha256', $nonce);
        if (Cache::store('sol_ads')->add($cacheKey, true, now()->addDays(self::SOL_TRACKING_DEDUPE_DAYS))) {
            try {
                $ad->increment('clicks_count');
            } catch (\Throwable $exception) {
                Cache::store('sol_ads')->forget($cacheKey);
                throw $exception;
            }
        }

        return redirect()->away($ad->destination_url)->header('Cache-Control', 'no-store, private');
    }

    private function incrementImpression(AdCampaign $ad): bool
    {
        if ($ad->frequency_limit !== null) {
            $periodStart = AdCampaign::periodStart($ad->frequency_period);
            DB::table('ad_campaign_period_impressions')->insertOrIgnore([
                'ad_campaign_id' => $ad->id,
                'frequency_period' => $ad->frequency_period,
                'period_start' => $periodStart,
                'impressions_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $incremented = DB::table('ad_campaign_period_impressions')
                ->where('ad_campaign_id', $ad->id)
                ->where('frequency_period', $ad->frequency_period)
                ->where('period_start', $periodStart)
                ->where('impressions_count', '<', $ad->frequency_limit)
                ->increment('impressions_count', 1, ['updated_at' => now()]);

            if ($incremented !== 1) {
                return false;
            }
        }

        $ad->increment('impressions_count');

        return true;
    }
}
