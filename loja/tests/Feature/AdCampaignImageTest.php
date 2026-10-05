<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertHeader('cache-control', 'public, max-age=3600');
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
