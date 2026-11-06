<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Cache::put('sg_active_clients_count', 0);
        $outputBufferLevel = ob_get_level();
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertSame(
            $outputBufferLevel,
            ob_get_level(),
            'The homepage Blade view must close its output buffer.'
        );
    }
}
