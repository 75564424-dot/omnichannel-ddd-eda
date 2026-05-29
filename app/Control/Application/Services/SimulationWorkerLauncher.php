<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Spawns the client-silo artisan worker for a fleet simulation run.
 *
 * The worker owns {@code simulation-worker-{runId}.log}; the control plane only writes
 * {@code simulation-dispatch-{runId}.log} to avoid Windows file-lock conflicts with {@code >>}.
 */
final class SimulationWorkerLauncher
{
    public function workerLogPath(string $runId): string
    {
        return storage_path('logs/simulation-worker-'.$runId.'.log');
    }

    public function dispatchLogPath(string $runId): string
    {
        return storage_path('logs/simulation-dispatch-'.$runId.'.log');
    }

    /**
     * @param list<string> $command
     * @param array<string, string> $workerEnv
     */
    public function launch(string $runId, array $command, array $workerEnv, string $envId, string $clientSlug): void
    {
        if ($runId === '') {
            throw new \InvalidArgumentException('runId vacío al despachar worker de simulación.');
        }

        $this->appendDispatchLog($runId, 'Dispatch: '.implode(' ', $command).PHP_EOL
            .'  APP_ENV='.$workerEnv['APP_ENV']
            .' PLATFORM_CONTROL_PLANE='.$workerEnv['PLATFORM_CONTROL_PLANE']
            .' PLATFORM_CLIENT_SLUG='.$workerEnv['PLATFORM_CLIENT_SLUG'].PHP_EOL);

        $php = $command[0] ?? ((new PhpExecutableFinder)->find(false) ?: 'php');
        $workerLog = $this->workerLogPath($runId);

        if (PHP_OS_FAMILY === 'Windows') {
            $this->launchWindows($runId, $envId, $clientSlug, $php, $workerLog, $workerEnv);

            return;
        }

        $process = new Process($command, base_path(), $workerEnv);
        $process->setTimeout(null);
        $process->start(function (string $type, string $buffer) use ($workerLog): void {
            if ($buffer !== '') {
                $this->appendWorkerLog($workerLog, $buffer);
            }
        });
    }

    /**
     * @param array<string, string> $workerEnv
     */
    private function launchWindows(
        string $runId,
        string $envId,
        string $clientSlug,
        string $php,
        string $workerLog,
        array $workerEnv,
    ): void {
        $launcherDir = storage_path('app/simulation-launchers');
        if (! is_dir($launcherDir)) {
            mkdir($launcherDir, 0755, true);
        }

        $batPath = $launcherDir.DIRECTORY_SEPARATOR.$runId.'.bat';
        $basePath = base_path();
        $quotedPhp = '"'.str_replace('"', '""', $php).'"';
        $quotedArtisan = '"'.str_replace('"', '""', $basePath.DIRECTORY_SEPARATOR.'artisan').'"';
        $quotedLog = '"'.str_replace('"', '""', $workerLog).'"';

        $envLines = [
            '@echo off',
            'set APP_ENV='.$envId,
        ];

        foreach ($workerEnv as $key => $value) {
            if ($key === 'APP_ENV') {
                continue;
            }
            $envLines[] = 'set '.$key.'='.$this->escapeBatEnvValue($value);
        }

        $envLines[] = 'cd /d "'.str_replace('/', '\\', $basePath).'"';

        $bat = implode("\r\n", array_merge($envLines, [
            'echo [%DATE% %TIME%] Worker start >>'.$quotedLog.' 2>&1',
            'echo DB_DATABASE=%DB_DATABASE% >>'.$quotedLog.' 2>&1',
            $quotedPhp.' '.$quotedArtisan.' platform:simulation:execute-run '.$runId
                .' --env='.$envId.' --no-ansi >>'.$quotedLog.' 2>&1',
        ]))."\r\n";

        file_put_contents($batPath, $bat);

        $quotedBat = '"'.str_replace('"', '""', $batPath).'"';
        Process::fromShellCommandline('cmd /C start /B cmd /C '.$quotedBat, $basePath)->run();

        $this->appendDispatchLog($runId, 'Launched: '.$batPath.PHP_EOL);
    }

    private function appendDispatchLog(string $runId, string $chunk): void
    {
        $path = $this->dispatchLogPath($runId);
        @file_put_contents($path, '['.now()->toDateTimeString().'] '.$chunk, FILE_APPEND | LOCK_EX);
    }

    private function appendWorkerLog(string $path, string $chunk): void
    {
        @file_put_contents($path, $chunk, FILE_APPEND | LOCK_EX);
    }

    private function escapeBatEnvValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/[\s"&|<>^]/', $value) === 1) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }
}
