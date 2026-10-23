<?php

namespace Tests\Feature;

use App\Models\AutovendaOrder;
use App\Models\VoucherPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SolAdvertisingEmbedTest extends TestCase
{
    use RefreshDatabase;

    public function test_advertising_page_allows_only_configured_https_sol_origins(): void
    {
        config(['services.sol.ads_frame_origins' => 'https://portal.sol.example, https://wifi.sol.example:8443']);
        $this->createPlan();

        $response = $this->get(route('ads.portal-wifi'))->assertOk();
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString(
            'frame-ancestors https://portal.sol.example https://wifi.sol.example:8443',
            $csp
        );
        $this->assertStringNotContainsString('https://untrusted.example', $csp);
        $this->assertFalse($response->headers->has('X-Frame-Options'));
        $this->assertSame(1, count($response->headers->all('content-security-policy')));
    }

    public function test_advertising_page_denies_framing_without_a_valid_sol_origin(): void
    {
        config(['services.sol.ads_frame_origins' => 'https://portal.sol.example/path, http://insecure.example']);

        $response = $this->get(route('ads.portal-wifi'))->assertOk();

        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $response->headers->get('Content-Security-Policy')
        );
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_public_ad_page_uses_only_local_assets_and_top_level_store_links(): void
    {
        config(['services.sol.ads_frame_origins' => 'https://portal.sol.example']);
        $this->createPlan();

        $response = $this->get(route('ads.portal-wifi'))->assertOk();
        $html = $response->getContent();
        $document = new \DOMDocument();
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//script | //link[@rel="stylesheet"]')->length);
        $this->assertSame(1, $xpath->query('//img[contains(@src, "/img/logo2.jpeg")]')->length);
        $this->assertFileExists(public_path('img/logo2.jpeg'));
        $this->assertSame(
            1,
            $xpath->query('//a[@target="_top" and contains(@href, "/checkout/sol-test")]')->length
        );
        $this->assertSame(2, $xpath->query('//a[@target="_top"]')->length);
        $this->assertStringNotContainsString('csrf-token', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_storefront_and_financial_pages_remain_protected_from_embedding(): void
    {
        config([
            'services.sol.ads_frame_origins' => 'https://portal.sol.example',
            'services.gpo.frame_token' => 'test-frame-credential',
            'services.gpo.frame_token_url' => 'https://emis.example/frameToken',
            'services.gpo.webframe_url' => 'https://emis.example/webframe',
        ]);
        Http::fake([
            'https://emis.example/frameToken' => Http::response([
                'id' => 'test-token',
                'timeToLive' => 300000,
            ]),
        ]);

        $home = $this->get('/')->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $home->headers->get('Content-Security-Policy')
        );

        $admin = $this->get('/admin');
        $admin->assertHeader('X-Frame-Options', 'DENY');

        $order = AutovendaOrder::create([
            'plan_id' => 'sol-test',
            'plan_name' => 'Plano de Teste',
            'quantity' => 1,
            'amount_aoa' => 200,
            'currency' => 'AOA',
            'customer_name' => 'Teste',
            'customer_email' => 'teste@example.ao',
            'customer_phone' => '244900000000',
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => AutovendaOrder::METHOD_GPO,
        ]);

        $payment = $this->get(route('gpo.show', $order))->assertOk();
        $payment->assertHeader('X-Frame-Options', 'DENY');
        $this->assertSame(1, count($payment->headers->all('content-security-policy')));
        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $payment->headers->get('Content-Security-Policy')
        );
        $this->assertStringContainsString(
            'frame-src https://pagamentonline.emis.co.ao',
            $payment->headers->get('Content-Security-Policy')
        );
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
}
