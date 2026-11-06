<?php

namespace Tests\Feature;

use App\Models\AutovendaOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GpoIntegrationSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_emis_frames_keep_the_sandbox_and_allow_only_user_activated_top_navigation(): void
    {
        $views = [
            'resources/views/store/pagamento-gpo.blade.php',
            'resources/views/pages/pagar-plano-gpo.blade.php',
            'resources/views/reseller/pagamento-gpo.blade.php',
            'resources/views/reseller/manutencao-gpo.blade.php',
        ];

        foreach ($views as $view) {
            $content = file_get_contents(base_path($view));
            $this->assertNotFalse($content, "Could not read {$view}");
            $this->assertMatchesRegularExpression('/sandbox="([^"]+)"/', $content, $view);
            $this->assertSame(1, preg_match_all('/<iframe\b/i', $content), $view);
            $this->assertSame(0, preg_match('/<frame\b/i', $content), $view);

            preg_match('/sandbox="([^"]+)"/', $content, $matches);
            $permissions = explode(' ', $matches[1]);

            $this->assertContains('allow-top-navigation-by-user-activation', $permissions, $view);
            $this->assertContains('allow-popups', $permissions, $view);
            $this->assertNotContains('allow-top-navigation', $permissions, $view);
            $this->assertNotContains('allow-top-navigation-to-custom-protocols', $permissions, $view);
        }
    }

    public function test_production_gpo_callback_fails_closed_without_confirmed_ip_allowlist(): void
    {
        $this->app->detectEnvironment(static fn () => 'production');
        config(['services.gpo.callback_ips' => '']);

        $this->postJson(route('webhooks.gpo'), [])->assertServiceUnavailable();
    }

    public function test_refresh_reuses_the_live_emis_purchase_token_and_reference(): void
    {
        config([
            'services.gpo.frame_token' => 'merchant-frame-token',
            'services.gpo.frame_token_url' => 'https://emis.example/frameToken',
            'services.gpo.webframe_url' => 'https://emis.example/webframe',
        ]);
        Http::fake([
            'https://emis.example/frameToken' => Http::response([
                'id' => 'purchase-token',
                'timeToLive' => 300000,
            ]),
        ]);
        $order = AutovendaOrder::create([
            'plan_id' => 'daily',
            'plan_name' => 'Plano Diário',
            'quantity' => 1,
            'amount_aoa' => 200,
            'currency' => 'AOA',
            'customer_name' => 'Teste',
            'customer_email' => 'teste@example.ao',
            'customer_phone' => '244900000000',
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => AutovendaOrder::METHOD_GPO,
        ]);

        $response = $this->get(route('gpo.show', $order))
            ->assertOk()
            ->assertSee('purchase-token')
            ->assertHeader('Content-Security-Policy')
            ->assertHeader('X-Frame-Options', 'DENY');
        $this->assertStringContainsString(
            'frame-src https://pagamentonline.emis.co.ao',
            $response->headers->get('Content-Security-Policy')
        );
        $document = new \DOMDocument();
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        $xpath = new \DOMXPath($document);
        $this->assertSame(
            0,
            $xpath->query(
                '//*[@id="gpoFrame"]/ancestor-or-self::*[@role="dialog" or self::dialog or contains(concat(" ", normalize-space(@class), " "), " modal ")]'
            )->length,
            'The EMIS iframe must not be launched from or embedded in a modal.'
        );
        $firstReference = $order->fresh()->payment_reference;
        $this->get(route('gpo.show', $order))->assertOk()->assertSee('purchase-token');

        $this->assertSame($firstReference, $order->fresh()->payment_reference);
        Http::assertSentCount(1);
    }

    public function test_expired_purchase_token_uses_a_new_reference_and_preserves_the_previous_one(): void
    {
        config([
            'services.gpo.frame_token' => 'merchant-frame-token',
            'services.gpo.frame_token_url' => 'https://emis.example/frameToken',
            'services.gpo.webframe_url' => 'https://emis.example/webframe',
        ]);
        Http::fake([
            'https://emis.example/frameToken' => Http::sequence()
                ->push(['id' => 'first-token', 'timeToLive' => 300000])
                ->push(['id' => 'second-token', 'timeToLive' => 300000]),
        ]);
        $order = AutovendaOrder::create([
            'plan_id' => 'daily',
            'plan_name' => 'Plano Diário',
            'quantity' => 1,
            'amount_aoa' => 200,
            'currency' => 'AOA',
            'customer_name' => 'Teste',
            'customer_email' => 'teste@example.ao',
            'customer_phone' => '244900000000',
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => AutovendaOrder::METHOD_GPO,
        ]);

        $this->get(route('gpo.show', $order))->assertOk()->assertSee('first-token');
        $firstReference = $order->fresh()->payment_reference;
        $this->travel(6)->minutes();
        $this->get(route('gpo.show', $order))->assertOk()->assertSee('second-token');

        $order->refresh();
        $this->assertNotSame($firstReference, $order->payment_reference);
        $this->assertContains($firstReference, $order->meta['gpo_references']);
        Http::assertSentCount(2);
    }
}
