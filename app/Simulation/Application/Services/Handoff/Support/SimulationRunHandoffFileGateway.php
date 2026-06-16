<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Handoff\Support;

/**
 * Atomic JSON file persistence for simulation run handoff payloads.
 */
final class SimulationRunHandoffFileGateway
{
    private function directory(): string
    {
        $dir = storage_path('app/simulation-handoff');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    private function path(string $runId): string
    {
        return $this->directory().DIRECTORY_SEPARATOR.$runId.'.json';
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function write(string $runId, array $payload): void
    {
        $path = $this->path($runId);
        $tmp  = $path.'.tmp';
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        file_put_contents($tmp, $json, LOCK_EX);
        rename($tmp, $path);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $runId, bool $useSharedLock = true): ?array
    {
        return $this->decodeFile($this->path($runId), $useSharedLock);
    }

    public function forget(string $runId): void
    {
        $path = $this->path($runId);
        if (is_file($path)) {
            unlink($path);
        }
    }

    public function purgeAll(): int
    {
        $count = 0;
        foreach (glob($this->directory().DIRECTORY_SEPARATOR.'*.json') ?: [] as $file) {
            if (is_file($file) && unlink($file)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeFile(string $path, bool $useSharedLock): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        if (! $useSharedLock) {
            $contents = @file_get_contents($path);
            if ($contents === false || $contents === '') {
                return null;
            }

            $decoded = json_decode($contents, true);

            return is_array($decoded) ? $decoded : null;
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            if (flock($handle, LOCK_SH)) {
                $contents = (string) stream_get_contents($handle);
                flock($handle, LOCK_UN);
            } else {
                $contents = (string) stream_get_contents($handle);
            }
        } finally {
            fclose($handle);
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }
}
