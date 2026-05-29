<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

/**
 * Builds environment variables for a detached client-silo simulation worker.
 */
final class SimulationWorkerEnvironmentFactory
{
    /**
     * @return array<string, string>
     */
    public function forClientSilo(string $envId, string $clientSlug): array
    {
        $env = [];
        foreach (array_merge($_ENV, $_SERVER) as $key => $value) {
            if (! is_string($key) || ! is_string($value) || $value === '') {
                continue;
            }
            $env[$key] = $value;
        }

        $env['APP_ENV'] = $envId;
        $env['PLATFORM_CONTROL_PLANE'] = 'false';
        $env['PLATFORM_CLIENT_SLUG'] = $clientSlug;
        $env['PLATFORM_CONTROL_PLANE_URL'] = (string) config('platform.simulation.control_plane_url', '');
        $env['PLATFORM_SIMULATION_INTERNAL_TOKEN'] = (string) config('platform.simulation.internal_token', '');

        return $env;
    }
}
