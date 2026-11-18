<?php

namespace Tests\Feature;

use App\Models\AutovendaOrder;
use App\Models\WifiCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GpoManualReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sg.admin_token' => 'test-admin-token']);
    }

    public function test_only_an_admin_with_matching_emis_evidence_can_deliver_once(): void
    {
        $order = $this->order('AW310TEST');
        WifiCode::create(['code' => 'VOUCHER-ONE', 'plan_id' => 'daily', 'status' => 'available']);
        WifiCode::create(['code' => 'VOUCHER-TWO', 'plan_id' => 'daily', 'status' => 'available']);
        $data = [
            'payment_reference' => 'AW310TEST',
            'amount_aoa' => 350,
            'transaction_id' => 'EMIS-TX-310',
            'confirmed_in_emis' => '1',
        ];

        $this->post(route('admin.autovenda.reconcile', $order), $data)
            ->assertRedirect(route('admin.login'));
        $this->assertFalse($order->fresh()->isPaid());

        $this->withSession(['sg_admin_authenticated' => true])
            ->get(route('admin.autovenda.index', ['q' => $order->id]))
            ->assertOk()
            ->assertSee('Confirmar na EMIS');

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.autovenda.reconcile', $order), array_merge($data, ['amount_aoa' => 349]))
            ->assertSessionHasErrors();
        $this->assertFalse($order->fresh()->isPaid());

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.autovenda.reconcile', $order), array_diff_key($data, ['confirmed_in_emis' => true]))
            ->assertSessionHasErrors();
        $this->assertFalse($order->fresh()->isPaid());

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.autovenda.reconcile', $order), $data)
            ->assertRedirect(route('admin.autovenda.index', ['q' => $order->id]));
        $this->assertSame('VOUCHER-ONE', $order->fresh()->wifi_code);
        $this->assertSame('EMIS-TX-310', $order->fresh()->manual_gpo_transaction_id);

        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.autovenda.reconcile', $order), $data)
            ->assertSessionHasErrors();
        $this->assertSame(1, WifiCode::where('status', 'used')->count());

        $this->travel(2)->minutes();
        $other = $this->order('AW311TEST');
        $this->withSession(['sg_admin_authenticated' => true])
            ->post(route('admin.autovenda.reconcile', $other), array_merge($data, ['payment_reference' => 'AW311TEST']))
            ->assertSessionHasErrors();
        $this->assertFalse($other->fresh()->isPaid());
    }

    private function order(string $reference): AutovendaOrder
    {
        return AutovendaOrder::create([
            'plan_id' => 'daily',
            'plan_name' => 'Plano Diário',
            'quantity' => 1,
            'amount_aoa' => 350,
            'currency' => 'AOA',
            'customer_name' => 'Teste',
            'customer_email' => '',
            'customer_phone' => '',
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => 'gpo',
            'payment_gateway' => 'gpo',
            'payment_reference' => $reference,
        ]);
    }
}
