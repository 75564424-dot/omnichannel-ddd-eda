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

        $payload = [
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
            'planned_total'     => (int) $run->planned_total,
        ];

        file_put_contents(
            $this->path($run->id),
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $runId): ?array
    {
        $path = $this->path($runId);
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
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

        file_put_contents(
            $this->path($runId),
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );
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
}
