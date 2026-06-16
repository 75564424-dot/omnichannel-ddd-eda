<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Shared\Platform\LocalInstanceEnvironmentLoader;
use Symfony\Component\Process\Process;

final class LocalFleetInstanceArtisanRunner
{
    public function __construct(
        private readonly LocalInstanceEnvironmentLoader $environmentLoader,
    ) {}

    public function bootstrapInstance(string $envId): void
    {
        $this->run($envId, 'migrate', ['force' => true]);
        $this->run($envId, 'platform:instance:bootstrap', ['skip-admin' => true]);
        $this->run($envId, 'db:seed', [
            'class' => 'Database\\Seeders\\MiddlewareDatabaseSeeder',
            'force' => true,
        ]);
    }

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function run(string $envId, string $command, array $arguments = []): void
    {
        $phpBinary = PHP_BINARY !== '' ? PHP_BINARY : 'php';
        if (str_contains(strtolower($phpBinary), 'php-cgi')) {
            $phpBinary = 'php';
        }
        $params = [
            $phpBinary,
            '-d',
            'register_argc_argv=1',
            '-d',
            'variables_order=EGPCS',
            'artisan',
            '--env='.$envId,
            $command,
        ];

        foreach ($arguments as $key => $value) {
            if (is_int($key)) {
                $params[] = (string) $value;
            } elseif ($value === true) {
                $params[] = '--'.$key;
            } elseif ($value !== false && $value !== null) {
                $params[] = '--'.$key.'='.$value;
            }
        }

        $envFileVars = $this->environmentLoader->load($envId);
        $env = array_merge($_SERVER, $_ENV, $envFileVars, ['APP_ENV' => $envId]);
        $env = array_filter($env, static fn ($value) => is_scalar($value) || $value === null);
        $process = new Process($params, base_path(), $env);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }
}
