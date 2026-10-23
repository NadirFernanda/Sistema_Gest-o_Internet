<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GpoAndroidFramePermissionsTest extends TestCase
{
    #[DataProvider('paymentViews')]
    public function test_emis_frame_allows_top_navigation_only_after_user_activation(string $view): void
    {
        $content = file_get_contents(base_path($view));

        $this->assertNotFalse($content, "Could not read {$view}");
        $this->assertSame(1, preg_match_all('/<iframe\b/i', $content), $view);
        $this->assertSame(1, preg_match_all('/sandbox="([^"]+)"/', $content, $matches), $view);

        $permissions = preg_split('/\s+/', trim($matches[1][0]));

        $this->assertContains('allow-scripts', $permissions, $view);
        $this->assertContains('allow-forms', $permissions, $view);
        $this->assertContains('allow-same-origin', $permissions, $view);
        $this->assertContains('allow-popups', $permissions, $view);
        $this->assertContains('allow-top-navigation-by-user-activation', $permissions, $view);
        $this->assertNotContains('allow-top-navigation', $permissions, $view);
    }

    public static function paymentViews(): array
    {
        return [
            'individual sale' => ['resources/views/store/pagamento-gpo.blade.php'],
            'family plan' => ['resources/views/pages/pagar-plano-gpo.blade.php'],
            'reseller purchase' => ['resources/views/reseller/pagamento-gpo.blade.php'],
            'reseller maintenance' => ['resources/views/reseller/manutencao-gpo.blade.php'],
        ];
    }
}
