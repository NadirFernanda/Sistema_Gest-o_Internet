<?php

namespace Tests\Feature;

use App\Models\AutovendaOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GpoFrameDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_page_has_opt_in_redacted_frame_diagnostics_without_changing_the_frame_source(): void
    {
        config([
            'services.gpo.frame_token' => 'test-merchant-token',
            'services.gpo.frame_token_url' => 'https://pagamentonline.emis.co.ao/frameToken',
            'services.gpo.webframe_url' => 'https://pagamentonline.emis.co.ao/webframe',
        ]);
        Http::fake([
            'https://pagamentonline.emis.co.ao/frameToken' => Http::response([
                'id' => 'test-purchase-token',
                'timeToLive' => 300000,
            ]),
        ]);

        $order = AutovendaOrder::create([
            'plan_id' => 'diagnostic-test',
            'plan_name' => 'Plano de diagnóstico',
            'quantity' => 1,
            'amount_aoa' => 200,
            'currency' => 'AOA',
            'customer_name' => 'Teste',
            'customer_email' => 'diagnostico@example.ao',
            'customer_phone' => '244900000000',
            'status' => AutovendaOrder::STATUS_AWAITING_PAYMENT,
            'payment_method' => AutovendaOrder::METHOD_GPO,
        ]);

        $response = $this->get(route('gpo.show', $order).'?gpo_diag=1')->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, preg_match_all('/<iframe\b/i', $html));
        $this->assertSame(1, preg_match_all('/id="gpoFrame"/', $html));
        $document = new \DOMDocument;
        $previousErrorMode = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorMode);

        $frame = (new \DOMXPath($document))->query('//iframe[@id="gpoFrame"]')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $frame);
        $frameUrl = parse_url($frame->getAttribute('src'));
        $this->assertSame('https', $frameUrl['scheme'] ?? null);
        $this->assertSame('pagamentonline.emis.co.ao', $frameUrl['host'] ?? null);
        $this->assertSame('/webframe/frame', $frameUrl['path'] ?? null);
        parse_str($frameUrl['query'] ?? '', $frameQuery);
        $this->assertSame(['token'], array_keys($frameQuery));

        $diagnosticStart = strpos($html, 'var diagnosticsEnabled =');
        $diagnosticEnd = strpos($html, '</script>', $diagnosticStart);
        $this->assertNotFalse($diagnosticStart);
        $this->assertNotFalse($diagnosticEnd);
        $diagnosticScript = substr($html, $diagnosticStart, $diagnosticEnd - $diagnosticStart);
        $this->assertLessThan(
            strpos($html, '<iframe'),
            strpos($html, 'window.gpoFrameDiagnostic = function')
        );
        $this->assertStringContainsString('gpo_diag=1', $diagnosticScript);
        $this->assertStringContainsString('if (!diagnosticsEnabled) return;', $diagnosticScript);
        $this->assertStringContainsString('window.gpoFrameDiagnostics = [];', $diagnosticScript);
        $this->assertStringContainsString('window.gpoFrameDiagnostics.push(entry)', $diagnosticScript);
        $this->assertStringContainsString('window.gpoFrameDiagnostics.length > 100', $diagnosticScript);
        $this->assertLessThan(
            strpos($diagnosticScript, 'window.gpoFrameDiagnostics = [];'),
            strpos($diagnosticScript, 'if (!diagnosticsEnabled) return;')
        );
        $this->assertStringNotContainsString('console.', $diagnosticScript);
        $this->assertStringNotContainsString('fetch(', $diagnosticScript);
        $this->assertStringNotContainsString('sendBeacon', $diagnosticScript);
        $this->assertStringNotContainsString('URLSearchParams', $diagnosticScript);
        $this->assertStringNotContainsString('token', $diagnosticScript);
        $this->assertStringNotContainsString('reference', $diagnosticScript);
        $this->assertStringNotContainsString('cookie', $diagnosticScript);
        $this->assertStringNotContainsString('customer', $diagnosticScript);
        $this->assertStringNotContainsString('wifiCode', $diagnosticScript);
        $this->assertStringContainsString("window.gpoFrameDiagnostic('iframe-load'", $html);
        $this->assertStringContainsString("window.gpoFrameDiagnostic('parent-csp-violation'", $html);
        $this->assertStringContainsString("window.gpoFrameDiagnostic('emis-postmessage'", $html);
        $this->assertStringContainsString('source_is_payment_frame', $html);
        $this->assertStringContainsString('has_data: Boolean(event.data)', $html);
        $this->assertStringNotContainsString('event.data)', $diagnosticScript);
        $this->assertStringNotContainsString('iframe.src', $diagnosticScript);
        $this->assertStringNotContainsString('blockedURI', $diagnosticScript);
        $this->assertStringNotContainsString('gpoFrame.src =', $html);
        $this->assertStringContainsString(
            'frame-src https://pagamentonline.emis.co.ao',
            $response->headers->get('Content-Security-Policy')
        );

        Http::assertSentCount(1);
    }
}
