<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Handoff\Support;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;

/**
 * Builds handoff JSON payloads for client worker dispatch and progress updates.
 */
final class SimulationRunHandoffPayloadMapper
{
    /**
     * @param array<string, mixed> $modulesCatalog
     *
     * @return array<string, mixed>
     */
    public function buildDispatchPayload(
        SimulationRunModel $run,
        TenantModel $tenant,
        array $modulesCatalog,
    ): array {
        $durationMinutes = max(1, (int) $run->duration_minutes);

        return [
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
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function applyProgress(array $payload, int $current, int $total, string $phase = 'publishing'): array
    {
        $payload['progress_current'] = max(0, $current);
        $payload['planned_total']    = max(1, $total);
        $payload['progress_percent'] = min(100, (int) round(($current / max(1, $total)) * 100));
        $payload['phase']            = $phase;
        $payload['progress_at']      = now()->toIso8601String();

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $terminalPayload
     *
     * @return array<string, mixed>
     */
    public function applyTerminal(array $payload, string $status, array $terminalPayload): array
    {
        $payload['terminal_status']  = $status;
        $payload['terminal_payload'] = $terminalPayload;
        $payload['terminal_at']      = now()->toIso8601String();
        $payload['phase']            = $status;

        return $payload;
    }
}
