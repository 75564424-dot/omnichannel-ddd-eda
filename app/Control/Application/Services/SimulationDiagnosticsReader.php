<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use Illuminate\Support\Str;

/**
 * Reads worker and dispatch logs for a simulation run.
 */
final class SimulationDiagnosticsReader
{
    public function __construct(
        private readonly SimulationWorkerLauncher $launcher,
    ) {}

    public function readAll(string $runId): string
    {
        $parts = [];
        foreach ([$this->launcher->workerLogPath($runId), $this->launcher->dispatchLogPath($runId)] as $path) {
            $chunk = $this->readFile($path);
            if ($chunk !== '') {
                $parts[] = $chunk;
            }
        }

        return implode(PHP_EOL, $parts);
    }

    public function excerpt(string $runId, int $maxLength = 4000): ?string
    {
        $text = Str::limit($this->readAll($runId), $maxLength);

        return $text !== '' ? $text : null;
    }

    private function readFile(string $path): string
    {
        if (! is_file($path)) {
            return '';
        }

        $contents = @file_get_contents($path);

        return is_string($contents) && $contents !== '' ? $contents : '';
    }
}
