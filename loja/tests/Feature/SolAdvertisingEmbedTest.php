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

    public function test_advertising_page_allows_only_the_three_confirmed_sol_origins(): void
    {
        config([
            'services.sol.ads_frame_origins' => implode(', ', [
                'https://na.solnms.net',
                'https://na.solnms.net:8443',
                'https://na.solnms.net:8445',
                'https://untrusted.example',
                'https://na.solnms.net:9443',
                'http://na.solnms.net',
            ]),
        ]);
        $this->createPlan();

        $response = $this->get(route('ads.portal-wifi'))->assertOk();
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString(
            'frame-ancestors https://na.solnms.net https://na.solnms.net:8443 https://na.solnms.net:8445',
            $csp
        );
        $this->assertStringNotContainsString('https://untrusted.example', $csp);
        $this->assertStringNotContainsString('https://na.solnms.net:9443', $csp);
        $this->assertStringNotContainsString('http://na.solnms.net', $csp);
        $this->assertFalse($response->headers->has('X-Frame-Options'));
        $this->assertSame(1, count($response->headers->all('content-security-policy')));
    }

    public function test_advertising_page_denies_framing_without_configuration_or_with_unapproved_origins(): void
    {
        config(['services.sol.ads_frame_origins' => 'https://untrusted.example']);

        $response = $this->get(route('ads.portal-wifi'))->assertOk();

        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $response->headers->get('Content-Security-Policy')
        );
        $response->assertHeader('X-Frame-Options', 'DENY');

        config(['services.sol.ads_frame_origins' => '']);
        $response = $this->get(route('ads.portal-wifi'))->assertOk();
        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $response->headers->get('Content-Security-Policy')
        );
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_public_ad_page_has_store_button_and_safe_top_level_navigation_fallback(): void
    {
        config(['services.sol.ads_frame_origins' => 'https://na.solnms.net']);
        $this->createPlan();

        $response = $this->get(route('ads.portal-wifi'))->assertOk();
        $html = $response->getContent();
        $document = new \DOMDocument;
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//link[@rel="stylesheet"]')->length);
        $this->assertSame(1, $xpath->query('//script[contains(@src, "/js/sol-ads.js")]')->length);
        $this->assertSame(1, $xpath->query('//img[contains(@src, "/img/logo2.jpeg")]')->length);
        $this->assertFileExists(public_path('img/logo2.jpeg'));
        $this->assertSame(
            1,
            $xpath->query('//button[@id="open-store" and normalize-space(.)="VER A LOJA"]')->length
        );
        $this->assertSame(
            1,
            $xpath->query('//a[@id="store-fallback-link" and @target="_top" and @href="https://angolawifi.ao/"]')->length
        );
        $this->assertSame(
            1,
            $xpath->query('//a[@target="_top" and contains(@href, "/checkout/sol-test")]')->length
        );
        $this->assertSame(3, $xpath->query('//a[@target="_top"]')->length);
        $this->assertStringNotContainsString('csrf-token', $html);

        $script = file_get_contents(public_path('js/sol-ads.js'));
        $this->assertStringContainsString("window.open(storeUrl, '_blank')", $script);
        $this->assertStringContainsString('opened.opener = null', $script);
        $this->assertStringContainsString('window.top.location.href = storeUrl', $script);
        $this->assertStringNotContainsString("'noopener'", $script);
        $this->assertStringNotContainsString('setTimeout', $script);
        $this->assertStringNotContainsString('fetch(', $script);
    }

    public function test_storefront_and_financial_pages_remain_protected_from_embedding(): void
    {
        config([
            'services.sol.ads_frame_origins' => 'https://na.solnms.net,https://na.solnms.net:8443,https://na.solnms.net:8445',
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
        $this->assertStringNotContainsString(
            'https://na.solnms.net',
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
