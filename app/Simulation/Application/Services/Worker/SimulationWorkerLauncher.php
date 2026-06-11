<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Worker;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Runs the client-silo simulation worker process.
 *
 * On Windows under php artisan serve, Symfony Process::start() children are terminated when the
 * dispatching job ends (Process destructor). Use launchBlocking() from the afterResponse job, or
 * launchDetached() which spawns via popen so the worker outlives the HTTP request.
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
    public function launchDetached(string $runId, array $command, array $workerEnv, string $envId): void
    {
        $this->logDispatch($runId, $command, $workerEnv);

        if (PHP_OS_FAMILY === 'Windows') {
            $this->launchWindowsDetached($runId, $command, $workerEnv);

            return;
        }

        $workerLog = $this->workerLogPath($runId);
        $process = new Process($command, base_path(), $workerEnv);
        $process->setTimeout(null);
        $process->start(function (string $type, string $buffer) use ($workerLog): void {
            if ($buffer !== '') {
                $this->appendWorkerLog($workerLog, $buffer);
            }
        });

        $this->appendDispatchLog($runId, 'Launched detached pid='.$process->getPid().PHP_EOL);
    }

    /**
     * Runs the worker inline until completion (reliable on Windows; blocks the caller).
     *
     * @param list<string> $command
     * @param array<string, string> $workerEnv
     */
    public function launchBlocking(string $runId, array $command, array $workerEnv): void
    {
        $this->logDispatch($runId, $command, $workerEnv);
        $this->runWorkerSynchronously($command, $workerEnv, $this->workerLogPath($runId), $runId);
    }

    /**
     * @param list<string> $command
     * @param array<string, string> $workerEnv
     */
    private function logDispatch(string $runId, array $command, array $workerEnv): void
    {
        if ($runId === '') {
            throw new \InvalidArgumentException('runId vacío al despachar worker de simulación.');
        }

        $this->appendDispatchLog($runId, 'Dispatch: '.implode(' ', $command).PHP_EOL
            .'  APP_ENV='.$workerEnv['APP_ENV']
            .' PLATFORM_CONTROL_PLANE='.$workerEnv['PLATFORM_CONTROL_PLANE']
            .' PLATFORM_CLIENT_SLUG='.$workerEnv['PLATFORM_CLIENT_SLUG'].PHP_EOL);
    }

    /**
     * @param list<string> $command
     * @param array<string, string> $workerEnv
     */
    private function launchWindowsDetached(string $runId, array $command, array $workerEnv): void
    {
        $php = $command[0] ?? ((new PhpExecutableFinder)->find(false) ?: 'php');
        $basePathWin = str_replace('/', '\\', base_path());
        $workerLogWin = str_replace('/', '\\', $this->workerLogPath($runId));
        $batPath = storage_path('app'.DIRECTORY_SEPARATOR.'simulation-worker-'.$runId.'.bat');

        $lines = ['@echo off'];
        foreach ($workerEnv as $key => $value) {
            if ($key === '') {
                continue;
            }
            $lines[] = 'set '.$key.'='.$this->escapeWindowsBatchValue($value);
        }

        $lines[] = 'cd /d "'.$basePathWin.'"';
        $lines[] = '"'.str_replace('"', '""', $php).'" '
            .'"'.str_replace('"', '""', $basePathWin.'\\artisan').'" '
            .'platform:simulation:execute-run '.$runId
            .' --env='.($workerEnv['APP_ENV'] ?? 'production')
            .' --no-ansi >> "'.str_replace('"', '""', $workerLogWin).'" 2>&1';

        if (@file_put_contents($batPath, implode("\r\n", $lines)."\r\n") === false) {
            throw new \RuntimeException('No se pudo escribir el script del worker: '.$batPath);
        }

        $quotedBat = '"'.str_replace('"', '""', str_replace('/', '\\', $batPath)).'"';
        pclose(popen('start /B "" '.$quotedBat, 'r'));
        $this->appendDispatchLog($runId, 'Launched Windows detached (bat): '.$batPath.PHP_EOL);
    }

    private function escapeWindowsBatchValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/[\s&|<>^()%!"\'=]/', $value) === 1) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    /**
     * @param array<string, string> $workerEnv
     * @param list<string> $command
     */
    private function runWorkerSynchronously(
        array $command,
        array $workerEnv,
        string $workerLog,
        string $runId,
    ): void {
        $this->appendWorkerLog($workerLog, '['.now()->toDateTimeString()."] Worker start (blocking)\n");

        $process = new Process($command, base_path(), $workerEnv);
        $process->setTimeout(null);
        $process->run(function (string $type, string $buffer) use ($workerLog): void {
            if ($buffer !== '') {
                $this->appendWorkerLog($workerLog, $buffer);
            }
        });

        $this->appendDispatchLog($runId, 'Blocking worker exit code: '.$process->getExitCode().PHP_EOL);

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Worker de simulación falló.');
        }
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
}
