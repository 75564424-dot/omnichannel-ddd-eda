<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Simulation;

use App\Console\Application\Services\Simulation\SimulateClientCommandOptions;
use App\Console\Application\Services\Simulation\SimulateClientOrchestrator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulateClientOrchestratorTest extends TestCase
{
    #[Test]
    public function missing_fixture_slugs_returns_available_list_for_unknown_slug(): void
    {
        $orchestrator = $this->app->make(SimulateClientOrchestrator::class);

        $available = $orchestrator->missingFixtureSlugs('unknown-client-xyz');

        $this->assertIsArray($available);
        $this->assertContains('retailco', $available);
        $this->assertContains('acmepos', $available);
    }

    #[Test]
    public function missing_fixture_slugs_is_null_for_known_fixture(): void
    {
        $orchestrator = $this->app->make(SimulateClientOrchestrator::class);

        $this->assertNull($orchestrator->missingFixtureSlugs('retailco'));
    }

    #[Test]
    public function simulate_delegates_with_zero_events_without_sync_or_validate(): void
    {
        $orchestrator = $this->app->make(SimulateClientOrchestrator::class);
        $options = new SimulateClientCommandOptions(
            slug: 'retailco',
            events: 0,
            perMinute: null,
            durationMinutes: 1,
            applyFixture: false,
            skipSync: true,
            skipValidate: true,
        );

        $result = $orchestrator->simulate($options);

        $this->assertSame('retailco', $result['slug']);
        $this->assertSame([], $result['validation_errors']);
        $this->assertSame(0, $result['published']);
        $this->assertNull($result['sync']);
    }
}
