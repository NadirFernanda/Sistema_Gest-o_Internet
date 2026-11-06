<?php

namespace Tests\Unit;

use App\Services\GpoService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GpoServiceTest extends TestCase
{
    public function test_purchase_token_uses_the_configured_emis_webframe_and_amount(): void
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

        $url = app(GpoService::class)->createPurchaseToken(200, 'AW1234AB', 'https://shop.example/callback');

        $this->assertSame('https://emis.example/webframe/frame?token=purchase-token', $url);
        Http::assertSent(fn ($request) => $request->url() === 'https://emis.example/frameToken'
            && $request['reference'] === 'AW1234AB'
            && $request['amount'] === '200.00'
            && $request['token'] === 'merchant-frame-token'
            && $request['callbackUrl'] === 'https://shop.example/callback');
    }

    public function test_purchase_token_details_include_the_gateway_ttl_for_safe_refreshes(): void
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

        $details = app(GpoService::class)->createPurchaseTokenDetails(
            200,
            'AW1234AB',
            'https://shop.example/callback'
        );

        $this->assertSame('https://emis.example/webframe/frame?token=purchase-token', $details['url']);
        $this->assertSame(300000, $details['time_to_live_ms']);
    }

    public function test_emis_transaction_response_is_accepted_only_for_an_accepted_payment(): void
    {
        $result = app(GpoService::class)->parseCallback([
            'id' => 'transaction-123',
            'status' => 'ACCEPTED',
            'transactionType' => 'PAYMENT',
            'merchantReferenceNumber' => 'AW1234AB',
            'amount' => 200.00,
            'currency' => 'AOA',
        ]);

        $this->assertTrue($result['successful']);
        $this->assertSame('AW1234AB', $result['merchant_ref']);
        $this->assertSame('PAYMENT', $result['transaction_type']);
        $this->assertSame('AOA', $result['currency']);
    }

    public function test_authorization_or_unconfirmed_states_are_not_marked_as_paid(): void
    {
        foreach ([
            ['status' => 'ACCEPTED', 'transactionType' => 'AUTHORIZATION'],
            ['status' => 'AUTHORIZED', 'transactionType' => 'PAYMENT'],
            ['status' => 'SUCCESS', 'transactionType' => 'PAYMENT'],
        ] as $payload) {
            $result = app(GpoService::class)->parseCallback($payload);
            $this->assertFalse($result['successful']);
        }
    }
}
