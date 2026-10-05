<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdTrackingController extends Controller
{
    public function image(int $campaign): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ad = AdCampaign::query()->findOrFail($campaign);
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

            if ($ad->frequency_limit !== null) {
                $periodStart = AdCampaign::periodStart($ad->frequency_period);
                $counter = DB::table('ad_campaign_period_impressions')
                    ->where('ad_campaign_id', $ad->id)
                    ->where('frequency_period', $ad->frequency_period)
                    ->where('period_start', $periodStart)
                    ->lockForUpdate()
                    ->first();

                if ($counter && $counter->impressions_count >= $ad->frequency_limit) {
                    return;
                }

                if ($counter) {
                    DB::table('ad_campaign_period_impressions')
                        ->where('id', $counter->id)
                        ->increment('impressions_count', 1, ['updated_at' => now()]);
                } else {
                    DB::table('ad_campaign_period_impressions')->insert([
                        'ad_campaign_id' => $ad->id,
                        'frequency_period' => $ad->frequency_period,
                        'period_start' => $periodStart,
                        'impressions_count' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $ad->increment('impressions_count');
            $request->session()->put($sessionKey, now()->timestamp);
        });

        return response()->noContent();
    }

    public function click(int $campaign): RedirectResponse
    {
        $ad = AdCampaign::query()->findOrFail($campaign);
        abort_unless(AdCampaign::running($ad->placement)->whereKey($ad->id)->exists(), 404);

        $ad->increment('clicks_count');

        return redirect()->away($ad->destination_url);
    }
}
