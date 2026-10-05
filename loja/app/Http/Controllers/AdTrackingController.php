<?php

namespace App\Http\Controllers;

use App\Models\AdCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdTrackingController extends Controller
{
    public function impression(Request $request, int $campaign): Response
    {
        $data = $request->validate([
            'placement' => ['required', 'string', 'in:' . implode(',', array_keys(AdCampaign::PLACEMENTS))],
        ]);

        $ad = AdCampaign::available($data['placement'])->findOrFail($campaign);
        $sessionKey = 'ad_impression_' . $ad->id;
        $lastRecorded = $request->session()->get($sessionKey);

        if (! is_numeric($lastRecorded) || (int) $lastRecorded < now()->subMinutes(30)->timestamp) {
            $ad->increment('impressions_count');
            $request->session()->put($sessionKey, now()->timestamp);
        }

        return response()->noContent();
    }

    public function click(int $campaign): RedirectResponse
    {
        $ad = AdCampaign::query()
            ->where('active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->findOrFail($campaign);

        $ad->increment('clicks_count');

        return redirect()->away($ad->destination_url);
    }
}
