<?php

namespace Tests\Feature;

use App\Models\AdCampaign;
use App\Models\VoucherPlan;
use App\Models\WifiCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CommercialAdsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.sg.admin_token' => 'test-sg-admin-secret']);
        Storage::fake('public');
    }

    public function test_sg_admin_can_manage_own_campaigns_without_commercial_configuration(): void
    {
        config([
            'services.ads_commercial.enabled' => false,
            'services.ads_commercial.secret' => '',
        ]);

        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.create'))
            ->assertOk()
            ->assertSee('value="own"', false)
            ->assertDontSee('value="sponsored"', false);

        $this->post(route('admin.ads.store'), $this->campaignData('own'))
            ->assertRedirect(route('admin.ads.index'));

        $campaign = AdCampaign::sole();
        $this->assertSame('own', $campaign->campaign_type);
        $this->assertSame('AngolaWiFi', $campaign->advertiser_name);

        $this->get(route('admin.ads.edit', $campaign->id))->assertOk();
        $this->put(route('admin.ads.update', $campaign->id), array_replace($this->campaignData('own', false), ['title' => 'Nova campanha própria']))
            ->assertRedirect(route('admin.ads.index'));
        $this->get(route('ads.portal-wifi'))->assertOk()->assertSee('Nova campanha própria');
        $this->get(route('ads.sol-image', $campaign->id))->assertOk();
        $this->delete(route('admin.ads.destroy', $campaign->id))->assertRedirect(route('admin.ads.index'));
        $this->assertDatabaseMissing('ad_campaigns', ['id' => $campaign->id]);
    }

    public function test_sg_admin_cannot_manage_or_serve_sponsored_campaigns_by_default(): void
    {
        config([
            'services.ads_commercial.enabled' => false,
            'services.ads_commercial.secret' => '',
        ]);
        $own = $this->existingCampaign('own', 'Campanha própria', 'ads/own.png');
        $sponsored = $this->existingCampaign('sponsored', 'Campanha reservada', 'ads/sponsored.png');

        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.index'))->assertOk()
            ->assertSee('Campanha própria')
            ->assertDontSee('Campanha reservada')
            ->assertDontSee('Autorização comercial separada');
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
        $this->put(route('admin.ads.update', $sponsored->id), $this->campaignData('own', false))->assertNotFound();
        $this->delete(route('admin.ads.destroy', $sponsored->id))->assertNotFound();
        $this->post(route('admin.ads.store'), $this->campaignData('sponsored'))->assertForbidden();
        $this->put(route('admin.ads.update', $own->id), $this->campaignData('sponsored', false))->assertForbidden();

        $this->get(route('ads.portal-wifi'))->assertOk()
            ->assertSee('Campanha própria')
            ->assertDontSee('Campanha reservada');
        $this->get(route('ads.sol-image', $sponsored->id))->assertNotFound();
        $this->get(route('ads.image', $sponsored->id))->assertNotFound();
        $this->get(route('ads.click', $sponsored->id))->assertNotFound();
        $this->postJson(route('ads.impression', $sponsored->id), ['placement' => AdCampaign::SOL_PORTAL_PLACEMENT])
            ->assertNotFound();
        $this->get($this->signedSolUrl('ads.sol-impression', $sponsored))->assertNotFound();
        $this->get($this->signedSolUrl('ads.sol-click', $sponsored))->assertNotFound();
        $this->assertDatabaseHas('ad_campaigns', ['id' => $sponsored->id, 'campaign_type' => 'sponsored']);
    }

    public function test_missing_commercial_secret_blocks_authorization_even_when_switch_is_on(): void
    {
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => '',
        ]);
        $sponsored = $this->existingCampaign('sponsored', 'Campanha reservada', 'ads/sponsored.png');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => 'submitted-secret'])
            ->assertNotFound();
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
        $this->get(route('ads.portal-wifi'))->assertOk()->assertDontSee('Campanha reservada');
    }

    public function test_legacy_campaign_without_an_explicit_type_fails_closed(): void
    {
        config([
            'services.ads_commercial.enabled' => false,
            'services.ads_commercial.secret' => '',
        ]);
        Storage::disk('public')->put('ads/legacy.png', $this->samplePng());
        $legacy = AdCampaign::create([
            'advertiser_name' => 'AngolaWiFi',
            'title' => 'Campanha antiga sem tipo explícito',
            'image_path' => 'ads/legacy.png',
            'destination_url' => 'https://angolawifi.ao/',
            'button_text' => 'Saber mais',
            'placement' => AdCampaign::SOL_PORTAL_PLACEMENT,
            'active' => true,
        ]);

        $this->assertSame('sponsored', $legacy->fresh()->campaign_type);
        $this->assertFalse(AdCampaign::available(AdCampaign::SOL_PORTAL_PLACEMENT)->whereKey($legacy->id)->exists());
        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.index'))->assertOk()
            ->assertDontSee('Campanha antiga sem tipo explícito');
        $this->get(route('admin.ads.edit', $legacy->id))->assertNotFound();
        $this->get(route('ads.portal-wifi'))->assertOk()->assertDontSee('Campanha antiga sem tipo explícito');
    }

    public function test_disabling_commercial_access_blocks_previously_authorized_sessions_and_endpoints(): void
    {
        $secret = str_repeat('s', 32);
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => $secret,
        ]);
        $own = $this->existingCampaign('own', 'Campanha própria', 'ads/own.png');
        $sponsored = $this->existingCampaign('sponsored', 'Campanha comercial', 'ads/sponsored.png');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertRedirect(route('admin.ads.index'));
        $this->get(route('admin.ads.edit', $sponsored->id))->assertOk();

        config(['services.ads_commercial.enabled' => false]);
        $this->get(route('admin.ads.index'))->assertOk()
            ->assertSee('Campanha própria')
            ->assertDontSee('Campanha comercial');
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
        $this->put(route('admin.ads.update', $sponsored->id), $this->campaignData('sponsored', false))->assertNotFound();
        $this->delete(route('admin.ads.destroy', $sponsored->id))->assertNotFound();
        $this->post(route('admin.ads.store'), $this->campaignData('sponsored'))->assertForbidden();
        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])->assertNotFound();
        $this->get(route('admin.ads.edit', $own->id))->assertOk();
        $this->get(route('ads.portal-wifi'))->assertOk()->assertSee('Campanha própria')->assertDontSee('Campanha comercial');
        $this->get(route('ads.sol-image', $sponsored->id))->assertNotFound();
        $this->get(route('ads.image', $sponsored->id))->assertNotFound();
        $this->get(route('ads.click', $sponsored->id))->assertNotFound();
        $this->postJson(route('ads.impression', $sponsored->id), ['placement' => AdCampaign::SOL_PORTAL_PLACEMENT])
            ->assertNotFound();
        $this->get($this->signedSolUrl('ads.sol-impression', $sponsored))->assertNotFound();
        $this->get($this->signedSolUrl('ads.sol-click', $sponsored))->assertNotFound();
    }

    public function test_commercial_credential_is_not_returned_or_flushed_to_old_input(): void
    {
        $secret = str_repeat('Q', 40);
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => $secret,
        ]);
        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.index'))->assertOk()->assertDontSee($secret);

        $submitted = str_repeat('Z', 513);
        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $submitted])
            ->assertRedirect(route('admin.ads.index'))
            ->assertDontSee($submitted);
        $this->assertArrayNotHasKey('commercial_secret', session()->get('_old_input', []));
        $this->get(route('admin.ads.index'))->assertOk()->assertDontSee($submitted)->assertDontSee($secret);

        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertRedirect(route('admin.ads.index'))
            ->assertDontSee($secret);
        $this->get(route('admin.ads.index'))->assertOk()->assertDontSee($secret);
    }

    public function test_commercial_authorization_limits_repeated_attempts(): void
    {
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => str_repeat('s', 32),
        ]);
        $this->withSession(['sg_admin_authenticated' => true]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => 'incorrect'])
                ->assertRedirect(route('admin.ads.index'));
        }
        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => 'incorrect'])
            ->assertStatus(429);
    }

    public function test_public_sol_url_stays_dynamic_when_commercial_access_is_switched_off(): void
    {
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => str_repeat('s', 32),
        ]);
        $own = $this->existingCampaign('own', 'Campanha própria', 'ads/own.png');
        $sponsored = $this->existingCampaign('sponsored', 'Campanha comercial', 'ads/sponsored.png');
        $own->update(['active' => false]);

        $url = route('ads.portal-wifi');
        $this->get($url)->assertOk()->assertSee('Campanha comercial');

        config(['services.ads_commercial.enabled' => false]);
        $own->update(['active' => true]);
        $this->get($url)->assertOk()
            ->assertHeader('cache-control', 'no-store, private')
            ->assertSee('Campanha própria')
            ->assertDontSee('Campanha comercial');
        $this->get(route('ads.sol-image', $sponsored->id))->assertNotFound();
        $this->assertSame('/publicidade/portal-wifi', parse_url($url, PHP_URL_PATH));
    }

    public function test_own_campaign_purchase_bonus_remains_available_without_commercial_access(): void
    {
        config([
            'services.ads_commercial.enabled' => false,
            'services.ads_commercial.secret' => '',
        ]);
        $purchasePlan = $this->voucherPlan('purchase');
        $bonusPlan = $this->voucherPlan('bonus');
        $campaign = $this->existingCampaign('own', 'Bónus AngolaWiFi', 'ads/bonus.png');
        $campaign->update(['bonus_plan_slug' => $bonusPlan->slug]);
        $campaign->qualifyingPlans()->sync([$purchasePlan->slug]);
        WifiCode::create([
            'code' => 'TEST-OWN-BONUS-001',
            'plan_id' => $bonusPlan->slug,
            'status' => WifiCode::STATUS_AVAILABLE,
        ]);

        $this->assertSame($campaign->id, AdCampaign::bonusForPurchasePlan($purchasePlan->slug)?->id);
    }

    public function test_separate_commercial_credential_grants_and_revokes_backend_access(): void
    {
        $secret = str_repeat('s', 32);
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => $secret,
        ]);
        $sponsored = $this->existingCampaign('sponsored', 'Campanha comercial', 'ads/sponsored.png');

        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => 'wrong'])
            ->assertRedirect(route('admin.ads.index'))
            ->assertSessionHasErrors('commercial_secret');
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();

        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertRedirect(route('admin.ads.index'));
        $this->get(route('admin.ads.index'))->assertOk()->assertSee('Campanha comercial');
        $this->get(route('admin.ads.edit', $sponsored->id))->assertOk();
        $this->get(route('admin.ads.create'))->assertOk()->assertSee('value="sponsored"', false);
        $this->put(route('admin.ads.update', $sponsored->id), array_replace($this->campaignData('sponsored', false), ['title' => 'Campanha comercial editada']))
            ->assertRedirect(route('admin.ads.index'));
        $this->post(route('admin.ads.store'), array_replace($this->campaignData('sponsored'), ['active' => '0']))
            ->assertRedirect(route('admin.ads.index'));
        $this->assertSame(2, AdCampaign::where('campaign_type', 'sponsored')->count());
        $this->get(route('ads.portal-wifi'))->assertOk()->assertSee('Campanha comercial editada');

        $this->post(route('admin.ads.commercial.revoke'))->assertRedirect(route('admin.ads.index'));
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
        $this->get(route('admin.ads.create'))->assertOk()->assertDontSee('value="sponsored"', false);
    }

    public function test_sg_login_after_logout_does_not_restore_commercial_access(): void
    {
        $secret = str_repeat('s', 32);
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => $secret,
        ]);
        $sponsored = $this->existingCampaign('sponsored', 'Campanha comercial', 'ads/sponsored.png');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertRedirect(route('admin.ads.index'));
        $this->get(route('admin.ads.edit', $sponsored->id))->assertOk();

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.login.submit'), ['password' => 'test-sg-admin-secret'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
    }

    public function test_commercial_secret_alone_cannot_bypass_sg_login(): void
    {
        $secret = str_repeat('s', 32);
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => $secret,
        ]);

        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertRedirect(route('admin.login'));
        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.ads.create'))
            ->assertOk()
            ->assertDontSee('value="sponsored"', false);
    }

    public function test_rotating_commercial_secret_invalidates_existing_grants(): void
    {
        $secret = str_repeat('s', 32);
        config([
            'services.ads_commercial.enabled' => true,
            'services.ads_commercial.secret' => $secret,
        ]);
        $sponsored = $this->existingCampaign('sponsored', 'Campanha comercial', 'ads/sponsored.png');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertRedirect(route('admin.ads.index'));
        $this->get(route('admin.ads.edit', $sponsored->id))->assertOk();

        config(['services.ads_commercial.secret' => str_repeat('r', 32)]);
        $this->get(route('admin.ads.edit', $sponsored->id))->assertNotFound();
        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => $secret])
            ->assertSessionHasErrors('commercial_secret');
        $this->post(route('admin.ads.commercial.authorize'), ['commercial_secret' => str_repeat('r', 32)])
            ->assertRedirect(route('admin.ads.index'));
        $this->get(route('admin.ads.edit', $sponsored->id))->assertOk();
    }

    private function existingCampaign(string $type, string $title, string $path): AdCampaign
    {
        Storage::disk('public')->put($path, $this->samplePng());

        return AdCampaign::create([
            'campaign_type' => $type,
            'advertiser_name' => $type === 'own' ? 'AngolaWiFi' : 'Anunciante fictício',
            'title' => $title,
            'image_path' => $path,
            'destination_url' => 'https://example.org/offer',
            'button_text' => 'Saber mais',
            'placement' => AdCampaign::SOL_PORTAL_PLACEMENT,
            'active' => true,
            'frequency_period' => 'day',
            'frequency_limit' => 100,
        ]);
    }

    private function campaignData(string $type, bool $includeImage = true): array
    {
        $data = [
            'campaign_type' => $type,
            'advertiser_name' => 'Anunciante fictício',
            'title' => 'Campanha de teste',
            'destination_url' => 'https://example.org/offer',
            'button_text' => 'Saber mais',
            'placement' => AdCampaign::SOL_PORTAL_PLACEMENT,
            'active' => '1',
            'frequency_period' => 'day',
            'frequency_limit' => 100,
        ];
        if ($includeImage) {
            $data['image'] = UploadedFile::fake()->image('campaign.png');
        }

        return $data;
    }

    private function signedSolUrl(string $route, AdCampaign $campaign): string
    {
        return URL::temporarySignedRoute(
            $route,
            now()->addMinutes(5),
            ['campaign' => $campaign->id, 'nonce' => str_repeat('a', 64)],
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

    private function voucherPlan(string $slug): VoucherPlan
    {
        return VoucherPlan::create([
            'slug' => $slug,
            'name' => 'Plano '.$slug,
            'validity_label' => '24 horas',
            'validity_minutes' => 1440,
            'speed_label' => 'Até 10 Mbps',
            'price_public_aoa' => 350,
            'price_reseller_aoa' => 250,
            'active' => true,
            'sort_order' => 1,
        ]);
    }
}
