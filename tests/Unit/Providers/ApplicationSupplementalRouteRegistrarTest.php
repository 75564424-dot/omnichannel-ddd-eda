<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\Registrars\ApplicationSupplementalRouteRegistrar;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApplicationSupplementalRouteRegistrarTest extends TestCase
{
    #[Test]
    public function registers_health_ready_and_simulation_internal_routes(): void
    {
        ApplicationSupplementalRouteRegistrar::register();

        $this->assertTrue(Route::has('health.ready'));

        $uris = collect(Route::getRoutes())->map(fn ($route) => $route->uri())->all();
        $this->assertContains('health/ready', $uris);
        $this->assertContains('control/internal/simulation-runs/{run}', $uris);
    }
}
