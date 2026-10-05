<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdCampaignImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_campaign_image_is_served_from_public_storage(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('ads/campaign.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pWQAAAAASUVORK5CYII=',
            true
        ));
        $campaign = $this->createCampaign('ads/campaign.png');

        $this->get(route('ads.image', $campaign))
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('cache-control', 'max-age=3600, public');
    }

    public function test_campaign_image_is_not_served_when_file_is_missing_or_campaign_is_inactive(): void
    {
        Storage::fake('public');
        $campaign = $this->createCampaign('ads/missing.jpg');

        $this->get(route('ads.image', $campaign))->assertNotFound();

        Storage::disk('public')->put('ads/inactive.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pWQAAAAASUVORK5CYII=',
            true
        ));
        $campaign->update(['image_path' => 'ads/inactive.png', 'active' => false]);

        $this->get(route('ads.image', $campaign))->assertNotFound();
    }

    public function test_frequency_limit_hides_campaign_after_period_quota_is_reached(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('ads/campaign.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pWQAAAAASUVORK5CYII=',
            true
        ));
        $campaign = $this->createCampaign('ads/campaign.png');
        $campaign->update([
            'frequency_period' => 'day',
            'frequency_limit' => 2,
        ]);

        $this->assertTrue(AdCampaign::available('home_banner')->whereKey($campaign->id)->exists());

        DB::table('ad_campaign_period_impressions')->insert([
            'ad_campaign_id' => $campaign->id,
            'frequency_period' => 'day',
            'period_start' => AdCampaign::periodStart('day'),
            'impressions_count' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(AdCampaign::available('home_banner')->whereKey($campaign->id)->exists());
        $this->get(route('ads.image', $campaign))->assertOk();
        $this->get(route('ads.click', $campaign))->assertRedirect('https://angolawifi.ao');

        $this->travelTo(now()->addDay());
        $this->assertTrue(AdCampaign::available('home_banner')->whereKey($campaign->id)->exists());
    }

    public function test_impression_tracking_stops_at_the_configured_period_limit(): void
    {
        $campaign = $this->createCampaign('ads/campaign.png');
        $campaign->update([
            'frequency_period' => 'day',
            'frequency_limit' => 2,
        ]);

        for ($impression = 0; $impression < 2; $impression++) {
            $this->withSession([
                'ad_impression_' . $campaign->id => now()->subMinutes(31)->timestamp,
            ])->postJson(route('ads.impression', $campaign), [
                'placement' => 'home_banner',
            ])->assertNoContent();
        }

        $this->assertSame(2, (int) DB::table('ad_campaign_period_impressions')
            ->where('ad_campaign_id', $campaign->id)
            ->where('frequency_period', 'day')
            ->where('period_start', AdCampaign::periodStart('day'))
            ->value('impressions_count'));
        $this->assertSame(2, $campaign->fresh()->impressions_count);

        $this->withSession([
            'ad_impression_' . $campaign->id => now()->subMinutes(31)->timestamp,
        ])->postJson(route('ads.impression', $campaign), [
            'placement' => 'home_banner',
        ])->assertNotFound();
    }

    private function createCampaign(string $imagePath): AdCampaign
    {
        return AdCampaign::create([
            'campaign_type' => 'own',
            'advertiser_name' => 'AngolaWiFi',
            'title' => 'Campanha de teste',
            'description' => 'Imagem de teste',
            'image_path' => $imagePath,
            'destination_url' => 'https://angolawifi.ao',
            'button_text' => 'Saber mais',
            'placement' => 'home_banner',
            'active' => true,
        ]);
    }
}
