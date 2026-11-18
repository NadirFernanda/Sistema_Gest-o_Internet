<?php

namespace Tests\Feature;

use App\Models\AutovendaOrder;
use App\Models\VoucherPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPhoneRequiredTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_missing_or_invalid_phone_before_creating_an_order(): void
    {
        $this->createPlan();

        foreach ([null, '', '123456789'] as $phone) {
            $response = $this->post(route('store.checkout.process'), [
                'plan_id' => 'diario',
                'payment_method' => 'gpo',
                'customer_phone' => $phone,
            ]);

            $response->assertSessionHasErrors('customer_phone');
            $this->assertSame(0, AutovendaOrder::count());
        }
    }

    public function test_checkout_accepts_a_local_phone_and_stores_it_with_country_code(): void
    {
        $this->createPlan();

        $this->post(route('store.checkout.process'), [
            'plan_id' => 'diario',
            'payment_method' => 'gpo',
            'customer_phone' => '923456789',
        ])->assertRedirect();

        $order = AutovendaOrder::sole();
        $this->assertSame('244923456789', $order->customer_phone);
        $this->assertSame(AutovendaOrder::STATUS_AWAITING_PAYMENT, $order->status);
    }

    private function createPlan(): void
    {
        VoucherPlan::create([
            'slug' => 'diario',
            'name' => 'Plano Diário',
            'validity_label' => '1 dia',
            'validity_minutes' => 1440,
            'speed_label' => '5 Mbps',
            'price_public_aoa' => 350,
            'price_reseller_aoa' => 200,
            'active' => true,
        ]);
    }
}
