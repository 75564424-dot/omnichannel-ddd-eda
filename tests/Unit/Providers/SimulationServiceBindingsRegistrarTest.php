<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\Registrars\SimulationServiceBindingsRegistrar;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationServiceBindingsRegistrarTest extends TestCase
{
    #[Test]
    public function singleton_classes_cover_simulation_pipeline(): void
    {
        $classes = SimulationServiceBindingsRegistrar::singletonClasses();

        $this->assertContains(
            \App\Simulation\Application\Services\Worker\SimulationWorkerLauncher::class,
            $classes,
        );
        $this->assertContains(
            \App\Simulation\Application\Services\Runtime\SimulationPublishScope::class,
            $classes,
        );
        $this->assertGreaterThanOrEqual(10, count($classes));
    }

    #[Test]
    public function register_resolves_key_simulation_services(): void
    {
        SimulationServiceBindingsRegistrar::register($this->app);

        $this->assertInstanceOf(
            \App\Simulation\Application\Services\Orchestration\SimulationRunQueryService::class,
            $this->app->make(\App\Simulation\Application\Services\Orchestration\SimulationRunQueryService::class),
        );
    }
}
