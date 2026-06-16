<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Prepare;

use App\Control\Infrastructure\Models\SimulationRunModel;
use App\Shared\Infrastructure\Models\TenantModel;

/**
 * Persists tenant.settings.last_simulation after a run finishes.
 */
final class SimulationTenantSettingsSync
{
    /**
     * @param array{published: int, queue_matches: int}|null $result
     */
    public function recordLastRun(TenantModel $tenant, SimulationRunModel $run, ?array $result = null): void
    {
        $published = $result['published'] ?? (int) $run->published;
        $queueMatches = $result['queue_matches'] ?? (int) $run->queue_matches;

        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['last_simulation'] = [
            'run_id'            => $run->id,
            'ran_at'            => now()->toDateTimeString(),
            'fixture_slug'      => $run->fixture_slug,
            'events_per_minute' => $run->events_per_minute,
            'duration_minutes'  => $run->duration_minutes,
            'planned_total'     => $run->planned_total,
            'published'         => $published,
            'queue_matches'     => $queueMatches,
            'has_report'        => true,
        ];
        $tenant->update(['settings' => $settings]);
    }

    public function recordPrepared(TenantModel $tenant, string $fixtureSlug): void
    {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['simulation_prepared_at'] = now()->toIso8601String();
        $settings['simulation_fixture_slug'] = $fixtureSlug;
        $tenant->update(['settings' => $settings]);
    }

    /**
     * @param array{published: int, queue_matches: int} $counts
     */
    public function recordInlineSummary(
        TenantModel $tenant,
        string $fixtureSlug,
        int $eventsPerMinute,
        int $durationMinutes,
        int $plannedTotal,
        array $counts,
    ): void {
        $settings = is_array($tenant->settings) ? $tenant->settings : [];
        $settings['last_simulation'] = [
            'ran_at'            => now()->toDateTimeString(),
            'fixture_slug'      => $fixtureSlug,
            'events_per_minute' => $eventsPerMinute,
            'duration_minutes'  => $durationMinutes,
            'planned_total'     => $plannedTotal,
            'published'         => $counts['published'],
            'queue_matches'     => $counts['queue_matches'],
            'has_report'        => false,
        ];
        $tenant->update(['settings' => $settings]);
    }
}
