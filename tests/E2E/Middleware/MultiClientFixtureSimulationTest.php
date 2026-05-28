<?php

declare(strict_types=1);

namespace Tests\E2E\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Nightly multi-client simulation gate (Plan_SimulacionClientes Fase 3).
 */
final class MultiClientFixtureSimulationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function all_versioned_client_fixtures_simulate_successfully(): void
    {
        foreach (['retailco', 'acmepos'] as $slug) {
            $this->artisan('platform:simulate-client', [
                'slug'     => $slug,
                '--events' => '3',
            ])->assertSuccessful();
        }
    }
}
