<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Shared\Platform\LocalInstanceEnvironmentLoader;

/**
 * Builds environment variables for a detached client-silo simulation worker.
 */
final class SimulationWorkerEnvironmentFactory
{
    public function __construct(
        private readonly LocalInstanceEnvironmentLoader $envLoader,
    ) {}

    /**
     * @return array<string, string>
     */
    public function forClientSilo(string $envId, string $clientSlug): array
    {
        $env = $this->envLoader->criticalForWorker($envId);

        $env['APP_ENV'] = $envId;
        $env['PLATFORM_CONTROL_PLANE'] = 'false';
        $env['PLATFORM_CLIENT_SLUG'] = $clientSlug;
        $env['PLATFORM_CONTROL_PLANE_URL'] = (string) config('platform.simulation.control_plane_url', '');
        $env['PLATFORM_SIMULATION_INTERNAL_TOKEN'] = (string) config('platform.simulation.internal_token', '');

        return $env;
    }
}
