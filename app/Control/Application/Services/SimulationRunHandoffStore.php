<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;

/**
 * Persists simulation run specs on disk so client workers can start without HTTP to control plane.
 */
final class SimulationRunHandoffStore
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
     * @param array<string, mixed> $modulesCatalog
     */
    public function write(
        SimulationRunModel $run,
        TenantModel $tenant,
        array $modulesCatalog,
    ): void {
        $durationMinutes = max(1, (int) $run->duration_minutes);

        $this->persist($run->id, [
            'run_id'            => $run->id,
            'tenant_slug'       => $tenant->slug,
            'events_per_minute' => (int) $run->events_per_minute,
            'duration_minutes'  => $durationMinutes,
            'planned_total'     => (int) $run->planned_total,
            'prepare_first'     => (bool) $run->prepare_first,
            'fixture_slug'      => $run->fixture_slug,
            'modules_catalog'   => $modulesCatalog,
            'deadline_at'       => now()->addMinutes($durationMinutes + 1)->toIso8601String(),
            'written_at'        => now()->toIso8601String(),
            'phase'             => 'dispatched',
            'progress_current'  => 0,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $runId): ?array
    {
        return $this->decodeFile($this->path($runId), useSharedLock: true);
    }

    /**
     * Fast read for control-plane polling (atomic rename writes on the worker side).
     *
     * @return array<string, mixed>|null
     */
    public function readForSync(string $runId): ?array
    {
        return $this->decodeFile($this->path($runId), useSharedLock: false);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function markTerminal(string $runId, string $status, array $payload): void
    {
        $data = $this->read($runId) ?? ['run_id' => $runId];
        $data['terminal_status']  = $status;
        $data['terminal_payload'] = $payload;
        $data['terminal_at']      = now()->toIso8601String();
        $data['phase']            = $status;

        $this->persist($runId, $data);
    }

    public function updateProgress(string $runId, int $current, int $total, string $phase = 'publishing'): void
    {
        $payload = $this->read($runId);
        if ($payload === null) {
            return;
        }

        $payload['progress_current'] = max(0, $current);
        $payload['planned_total']    = max(1, $total);
        $payload['progress_percent'] = min(100, (int) round(($current / max(1, $total)) * 100));
        $payload['phase']            = $phase;
        $payload['progress_at']      = now()->toIso8601String();

        $this->persist($runId, $payload);
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
     * @param array<string, mixed> $payload
     */
    private function persist(string $runId, array $payload): void
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
