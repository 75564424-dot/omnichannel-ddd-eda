<?php

declare(strict_types=1);

namespace App\Shared\Platform;

/**
 * Loads variables from .env.{instanceId} for detached client-silo workers (Windows .bat).
 */
final class LocalInstanceEnvironmentLoader
{
    /** @var list<string> */
    private const CRITICAL_KEYS = [
        'APP_KEY',
        'APP_NAME',
        'APP_URL',
        'DB_CONNECTION',
        'DB_DATABASE',
        'CACHE_STORE',
        'SESSION_DRIVER',
        'SESSION_COOKIE',
        'SESSION_XSRF_COOKIE',
        'QUEUE_CONNECTION',
        'PLATFORM_DEPLOYMENT_MODE',
        'PLATFORM_CLIENT_SLUG',
        'PLATFORM_CLIENT_NAME',
        'PLATFORM_CONTROL_PLANE',
        'PLATFORM_CONTROL_PLANE_URL',
        'PLATFORM_SIMULATION_INTERNAL_TOKEN',
        'PLATFORM_SEED_INSTANCE_TENANT',
    ];

    /**
     * @return array<string, string>
     */
    public function load(string $envId): array
    {
        $path = base_path('.env.'.$envId);
        if (! is_file($path)) {
            return [];
        }

        $vars = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $value = trim($value, "\"'");
            }

            if ($key !== '') {
                $vars[$key] = $value;
            }
        }

        return $vars;
    }

    /**
     * @return array<string, string>
     */
    public function criticalForWorker(string $envId): array
    {
        $fromFile = $this->load($envId);
        $picked = [];

        foreach (self::CRITICAL_KEYS as $key) {
            if (isset($fromFile[$key]) && $fromFile[$key] !== '') {
                $picked[$key] = $fromFile[$key];
            }
        }

        return $picked;
    }
}
