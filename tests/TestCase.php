<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure common test permissions exist to avoid repeating in individual tests
        try {
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                if (!\Spatie\Permission\Models\Permission::where('name', 'clientes.devolucao')->exists()) {
                    \Spatie\Permission\Models\Permission::create(['name' => 'clientes.devolucao']);
                }
            }
        } catch (\Throwable $e) {
            // ignore; test environment may not have spatie tables
        }
    }
}
