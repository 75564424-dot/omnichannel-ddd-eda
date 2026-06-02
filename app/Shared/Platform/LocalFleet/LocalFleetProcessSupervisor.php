<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

use App\Shared\Platform\LocalInstanceEnvironmentLoader;
use Illuminate\Support\Facades\Log;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetProcessSupervisorInterface;

final class LocalFleetProcessSupervisor implements LocalFleetProcessSupervisorInterface
{
    /**
     * Checks if the instance process is currently listening on its port.
     */
    public function isRunning(string $envId, int $port): bool
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.2);

        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }

        return false;
    }

    /**
     * Ensures that the isolated PHP serve process is running for the given environment and port.
     */
    public function ensureRunning(string $envId, int $port): bool
    {
        if ($this->isRunning($envId, $port)) {
            Log::info("Local silo instance {$envId} is already running on port {$port}.");
            return true;
        }

        $artisan = base_path('artisan');
        $php = PHP_BINARY ?: 'php';
        if (str_contains(strtolower($php), 'php-cgi')) {
            $php = 'php';
        }

        $workerEnv = (new LocalInstanceEnvironmentLoader())->criticalForWorker($envId);
        $workerEnv['APP_ENV'] = $envId;

        if (PHP_OS_FAMILY === 'Windows') {
            $setCommands = [];
            foreach ($workerEnv as $key => $value) {
                if (! is_string($key) || $key === '') {
                    continue;
                }
                $setCommands[] = sprintf('set "%s=%s"', $key, str_replace('"', '\"', (string) $value));
            }
            $setPrefix = $setCommands !== [] ? implode(' && ', $setCommands).' && ' : '';
            $command = sprintf(
                '%sstart /B "" "%s" "%s" --env=%s serve --host=127.0.0.1 --port=%d > NUL 2>&1',
                $setPrefix,
                $php,
                $artisan,
                $envId,
                $port
            );
            pclose(popen($command, 'r'));
        } else {
            $prefix = '';
            foreach ($workerEnv as $key => $value) {
                if (! is_string($key) || $key === '') {
                    continue;
                }
                $prefix .= sprintf('%s=%s ', $key, escapeshellarg((string) $value));
            }
            $command = sprintf(
                '%s"%s" "%s" --env=%s serve --host=127.0.0.1 --port=%d > /dev/null 2>&1 &',
                $prefix,
                $php,
                $artisan,
                $envId,
                $port
            );
            exec($command);
        }

        Log::info("Spawned detached PHP serve process for silo {$envId} on port {$port}.");

        // Give the process a short moment to boot and verify
        for ($i = 0; $i < 10; $i++) {
            usleep(150000); // 150ms
            if ($this->isRunning($envId, $port)) {
                return true;
            }
        }

        Log::warning("Process spawned but port {$port} did not become active in time for {$envId}.");
        return false;
    }

    /**
     * Attempts to stop the running server on the specified port.
     * Note: In local development, the simplest, cleanest way is to find the process using the port
     * and terminate it.
     */
    public function stop(string $envId, int $port): bool
    {
        if (! $this->isRunning($envId, $port)) {
            return true;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            // Find process listening on port and taskkill it
            $output = [];
            exec(sprintf('netstat -ano | findstr :%d', $port), $output);
            foreach ($output as $line) {
                if (preg_match('/LISTENING\s+(\d+)/i', $line, $matches) === 1) {
                    $pid = (int) $matches[1];
                    if ($pid > 0) {
                        exec(sprintf('taskkill /F /PID %d > NUL 2>&1', $pid));
                    }
                }
            }
        } else {
            // Find process on Linux and kill it
            $pid = exec(sprintf('lsof -t -i:%d', $port));
            if ($pid !== '') {
                $pidInt = (int) $pid;
                if ($pidInt > 0) {
                    exec(sprintf('kill -9 %d > /dev/null 2>&1', $pidInt));
                }
            }
        }

        // Verify it stopped
        for ($i = 0; $i < 5; $i++) {
            usleep(100000); // 100ms
            if (! $this->isRunning($envId, $port)) {
                return true;
            }
        }

        return false;
    }
}
