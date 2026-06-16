<?php

declare(strict_types=1);

namespace App\Console\Application\Services\Simulation;

use App\Shared\Platform\Services\ClientFixtureLoader;
use App\Shared\Platform\Services\ClientSimulationService;

/**
 * ACL for platform:simulate-client — isolates Shared BC services from the Artisan command.
 */
final class SimulateClientOrchestrator
{
    public function __construct(
        private readonly ClientSimulationService $simulation,
        private readonly ClientFixtureLoader $fixtures,
    ) {}

    /**
     * @return list<string>|null Available slugs when fixture is missing; null when present.
     */
    public function missingFixtureSlugs(string $slug): ?array
    {
        if ($this->fixtures->exists($slug)) {
            return null;
        }

        return $this->fixtures->availableSlugs();
    }

    public function applyFixtureToFilesystem(string $slug): void
    {
        $this->fixtures->applyToFilesystem($slug);
    }

    /**
     * @return array<string, mixed>
     */
    public function simulate(SimulateClientCommandOptions $options): array
    {
        return $this->simulation->simulate(
            slug: $options->slug,
            events: $options->events,
            applyFixture: false,
            skipValidate: $options->skipValidate,
            skipSync: $options->skipSync,
            eventsPerMinute: $options->perMinute,
            durationMinutes: $options->perMinute !== null ? $options->durationMinutes : null,
        );
    }
}
