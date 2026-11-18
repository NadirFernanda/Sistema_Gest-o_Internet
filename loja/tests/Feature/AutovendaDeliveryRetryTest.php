<?php

namespace Tests\Feature;

use App\Mail\AutovendaWifiCodeMail;
use App\Models\AutovendaOrder;
use App\Models\WifiCode;
use App\Services\AutovendaOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AutovendaDeliveryRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_whatsapp_delivery_is_retried_without_allocating_another_voucher(): void
    {
        config([
            'services.evolution.enabled' => true,
            'services.evolution.url' => 'https://evolution.example',
            'services.evolution.key' => 'test-key',
            'services.evolution.instance' => 'test-instance',
        ]);
        Http::fake([
            'https://evolution.example/message/sendText/test-instance' => Http::sequence()
                ->push(['error' => 'temporary failure'], 500)
                ->push(['id' => 'sent'], 200),
        ]);
        Mail::fake();

        $order = AutovendaOrder::create([
            'plan_id' => 'diario',
            'plan_name' => 'Plano Diário',
            'amount_aoa' => 350,
            'customer_phone' => '244923456789',
            'customer_email' => 'cliente@example.com',
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => AutovendaOrder::METHOD_GPO,
        ]);
        WifiCode::create(['code' => 'WIFI-RETRY-001', 'plan_id' => 'diario', 'status' => WifiCode::STATUS_AVAILABLE]);
        WifiCode::create(['code' => 'WIFI-RETRY-002', 'plan_id' => 'diario', 'status' => WifiCode::STATUS_AVAILABLE]);

        app(AutovendaOrderService::class)->confirmPaymentAndDeliver($order);

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertSame('WIFI-RETRY-001', $order->wifi_code);
        $this->assertFalse($order->delivered_via_whatsapp);
        $this->assertTrue($order->delivered_via_email);
        $this->assertSame(1, $order->delivery_retry_count);
        Mail::assertSent(AutovendaWifiCodeMail::class, 1);

        $this->artisan('autovenda:retry-delivery')->assertSuccessful();
        Http::assertSentCount(1);

        $this->travel(6)->minutes();
        $this->artisan('autovenda:retry-delivery')->assertSuccessful();

        $order->refresh();
        $this->assertTrue($order->delivered_via_whatsapp);
        $this->assertNull($order->delivery_next_attempt_at);
        $this->assertSame(2, $order->delivery_retry_count);
        $this->assertSame(1, WifiCode::where('status', WifiCode::STATUS_USED)->count());
        Http::assertSentCount(2);
        Mail::assertSent(AutovendaWifiCodeMail::class, 1);

        $this->artisan('autovenda:retry-delivery')->assertSuccessful();
        Http::assertSentCount(2);
    }

    public function test_retries_stop_after_five_failed_attempts(): void
    {
        config([
            'services.evolution.enabled' => true,
            'services.evolution.url' => 'https://evolution.example',
            'services.evolution.key' => 'test-key',
            'services.evolution.instance' => 'test-instance',
        ]);
        Http::fake(['https://evolution.example/*' => Http::response([], 503)]);

        $order = AutovendaOrder::create([
            'plan_id' => 'diario',
            'plan_name' => 'Plano Diário',
            'amount_aoa' => 350,
            'customer_phone' => '244923456789',
            'status' => AutovendaOrder::STATUS_PAID,
            'wifi_code' => 'WIFI-RETRY-003',
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->artisan('autovenda:retry-delivery')->assertSuccessful();
            $this->travel(3)->hours();
        }

        $this->assertSame(5, $order->fresh()->delivery_retry_count);
        $this->assertFalse($order->fresh()->delivered_via_whatsapp);
        Http::assertSentCount(5);
    }
}
