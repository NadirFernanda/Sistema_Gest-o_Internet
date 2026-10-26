<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use App\Models\VoucherPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SolAdCampaignIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sol_campaign_placement_is_available_in_the_admin_form(): void
    {
        config(['services.sg.admin_token' => 'test-admin-token']);

        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.create'))
            ->assertOk()
            ->assertSee('value="' . AdCampaign::SOL_PORTAL_PLACEMENT . '"', false)
            ->assertSee('Portal WiFi / SOL');
    }

    public function test_sol_campaign_requires_a_frequency_period_and_limit(): void
    {
        config(['services.sg.admin_token' => 'test-admin-token']);
        Storage::fake('public');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.ads.store'), [
                'campaign_type' => 'sponsored',
                'advertiser_name' => 'Anunciante de teste',
                'title' => 'Campanha SOL de teste',
                'image' => UploadedFile::fake()->image('sol.jpg'),
                'destination_url' => 'https://advertiser.example/offer',
                'button_text' => 'Saber mais',
                'placement' => AdCampaign::SOL_PORTAL_PLACEMENT,
                'active' => true,
            ])
            ->assertSessionHasErrors(['frequency_period', 'frequency_limit']);
    }

    public function test_active_sol_campaign_is_rendered_with_local_image_and_safe_links(): void
    {
        config(['services.sol.ads_frame_origins' => 'https://na.solnms.net']);
        Storage::fake('public');
        Storage::disk('public')->put('ads/sol.png', $this->samplePng());
        $campaign = $this->createCampaign();

        $response = $this->get(route('ads.portal-wifi'))->assertOk();
        $response->assertHeader('cache-control', 'no-store, private');
        $response->assertHeaderMissing('set-cookie');
        $response->assertSee('Campanha SOL de teste');
        $response->assertSee(route('ads.sol-image', $campaign->id), false);
        $response->assertSee('data-impression-url=', false);
        $this->assertStringNotContainsString('expires=', $response->getContent());

        $document = new \DOMDocument;
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);
        $xpath = new \DOMXPath($document);

        $this->assertSame(1, $xpath->query('//article[contains(@class,"campaign")]//img')->length);
        $imageUrl = $xpath->query('//article[contains(@class,"campaign")]//img')->item(0)->getAttribute('src');
        $this->assertStringStartsWith('/ads/sol/' . $campaign->id . '/image', parse_url($imageUrl, PHP_URL_PATH));
        $impressionUrl = $xpath->query('//article[contains(@class,"campaign")]')->item(0)->getAttribute('data-impression-url');
        $this->assertStringStartsWith('/ads/sol/' . $campaign->id . '/impression/', $impressionUrl);
        $this->assertSame(
            1,
            $xpath->query('//article[contains(@class,"campaign")]//a[@target="_blank" and contains(@rel,"noopener") and contains(@href,"/ads/sol/") and contains(@href,"/click/")]')->length
        );
        $clickUrl = $xpath->query('//article[contains(@class,"campaign")]//a[@target="_blank"]')->item(0)->getAttribute('href');
        $this->assertStringStartsWith('/ads/sol/' . $campaign->id . '/click/', $clickUrl);
        $this->assertSame(
            1,
            $xpath->query('//article[contains(@class,"campaign")]//a[@target="_top" and normalize-space(.)="Abrir nesta janela"]')->length
        );
        $this->assertStringContainsString("connect-src 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("img-src 'self'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("script-src 'self'", $response->headers->get('Content-Security-Policy'));

        $script = file_get_contents(public_path('js/sol-ads.js'));
        $this->assertStringContainsString('new Image()', $script);
        $this->assertStringContainsString('sessionStorage', $script);
        $this->assertStringNotContainsString('fetch(', $script);

        $visitorLogCount = DB::table('visitor_logs')->count();
        $this->get(route('ads.sol-image', $campaign->id))
            ->assertOk()
            ->assertHeader('cache-control', 'max-age=3600, public')
            ->assertHeaderMissing('set-cookie');
        $this->assertSame($visitorLogCount, DB::table('visitor_logs')->count());
    }

    public function test_inactive_or_expired_sol_campaign_falls_back_to_plans(): void
    {
        $this->createPlan();
        $inactive = $this->createCampaign(['active' => false]);

        $this->get(route('ads.portal-wifi'))
            ->assertOk()
            ->assertSee('Plano SOL Teste')
            ->assertDontSee('Campanha SOL de teste');

        $inactive->update(['active' => true, 'ends_at' => now()->subMinute()]);

        $this->get(route('ads.portal-wifi'))
            ->assertOk()
            ->assertSee('Plano SOL Teste')
            ->assertDontSee('Campanha SOL de teste');
    }

    public function test_campaign_with_missing_creative_falls_back_to_plans(): void
    {
        $this->createPlan();
        $this->createCampaign();

        $this->get(route('ads.portal-wifi'))
            ->assertOk()
            ->assertSee('Plano SOL Teste')
            ->assertDontSee('Campanha SOL de teste');
    }

    public function test_signed_sol_impressions_are_deduplicated_and_obey_frequency_limits(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();
        $campaign = $this->createCampaign([
            'frequency_period' => 'day',
            'frequency_limit' => 1,
        ]);
        $pixelUrl = $this->signedUrl('ads.sol-impression', $campaign, str_repeat('a', 64));

        $this->get($pixelUrl)
            ->assertOk()
            ->assertHeader('content-type', 'image/gif')
            ->assertHeader('cache-control', 'no-store, private');
        $this->get($pixelUrl)->assertOk();

        $this->assertSame(1, (int) $campaign->fresh()->impressions_count);
        $this->assertSame(1, (int) DB::table('ad_campaign_period_impressions')
            ->where('ad_campaign_id', $campaign->id)
            ->where('frequency_period', 'day')
            ->value('impressions_count'));

        $otherViewerUrl = $this->signedUrl('ads.sol-impression', $campaign, str_repeat('e', 64));
        $this->get($otherViewerUrl)->assertNotFound();
        $this->assertSame(1, (int) $campaign->fresh()->impressions_count);

        $this->get(route('ads.portal-wifi'))
            ->assertOk()
            ->assertDontSee('Campanha SOL de teste');
    }

    public function test_signed_sol_tracking_urls_do_not_expire_after_fifteen_minutes(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();
        $campaign = $this->createCampaign();
        $url = $this->signedUrl('ads.sol-impression', $campaign, str_repeat('b', 64));

        $this->get($url . '&tampered=1')->assertForbidden();
        $this->travel(16)->minutes();

        $this->get($url)
            ->assertOk()
            ->assertHeaderMissing('set-cookie');

        $clickUrl = $this->signedUrl('ads.sol-click', $campaign, str_repeat('c', 64));
        $this->travel(16)->minutes();
        $this->get($clickUrl)
            ->assertRedirect('https://advertiser.example/offer')
            ->assertHeader('cache-control', 'no-store, private')
            ->assertHeaderMissing('set-cookie');
    }

    public function test_sol_click_counts_once_and_redirects_to_the_campaign_destination(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();
        $campaign = $this->createCampaign();
        $clickUrl = $this->signedUrl('ads.sol-click', $campaign, str_repeat('d', 64));
        $this->assertStringNotContainsString('expires=', $clickUrl);

        $this->get($clickUrl)->assertRedirect('https://advertiser.example/offer');
        $this->get($clickUrl)->assertRedirect('https://advertiser.example/offer');

        $this->assertSame(1, (int) $campaign->fresh()->clicks_count);
    }

    private function createCampaign(array $overrides = []): AdCampaign
    {
        return AdCampaign::create(array_merge([
            'campaign_type' => 'sponsored',
            'advertiser_name' => 'Anunciante de teste',
            'title' => 'Campanha SOL de teste',
            'description' => 'Descrição da campanha',
            'image_path' => 'ads/sol.png',
            'destination_url' => 'https://advertiser.example/offer',
            'button_text' => 'Saber mais',
            'placement' => AdCampaign::SOL_PORTAL_PLACEMENT,
            'active' => true,
        ], $overrides));
    }

    private function createPlan(): VoucherPlan
    {
        return VoucherPlan::create([
            'slug' => 'sol-test',
            'name' => 'Plano SOL Teste',
            'validity_label' => '24 horas',
            'validity_minutes' => 1440,
            'speed_label' => 'Até 10 Mbps',
            'price_public_aoa' => 350,
            'price_reseller_aoa' => 250,
            'active' => true,
            'sort_order' => 1,
        ]);
    }

    private function signedUrl(string $route, AdCampaign $campaign, string $nonce): string
    {
        return URL::signedRoute(
            $route,
            ['campaign' => $campaign->id, 'nonce' => $nonce],
            null,
            false
        );
    }

    private function samplePng(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pWQAAAAASUVORK5CYII=',
            true
        );
    }
}
