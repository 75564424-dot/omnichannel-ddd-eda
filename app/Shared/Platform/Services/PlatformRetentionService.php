<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Purges aged rows from high-volume middleware tables (Plan_BaseDeDatos.md).
 */
final class PlatformRetentionService
{
    /** @var list<string> */
    private const PURGEABLE = [
        'message_queue',
        'event_logs',
        'observability_metrics',
        'trace_logs',
        'event_store',
        'audit_logs',
    ];

    /**
     * @return array<string, array{days: int, deleted: int, cutoff: string|null, skipped?: bool}>
     */
    public function purge(bool $dryRun = false, ?string $onlyTable = null): array
    {
        $results = [];

        foreach (self::PURGEABLE as $table) {
            if ($onlyTable !== null && $onlyTable !== $table) {
                continue;
            }

            if (! Schema::hasTable($table)) {
                $results[$table] = ['days' => 0, 'deleted' => 0, 'cutoff' => null, 'skipped' => true];

                continue;
            }

            $days   = $this->retentionDays($table);
            $column = $this->timestampColumn($table);
            $cutoff = now()->subDays($days);

            $query = DB::table($table)->where($column, '<', $cutoff);

            if ($dryRun) {
                $results[$table] = [
                    'days'    => $days,
                    'deleted' => $query->count(),
                    'cutoff'  => $cutoff->toIso8601String(),
                ];

                continue;
            }

            $deleted = $query->delete();
            $results[$table] = [
                'days'    => $days,
                'deleted' => $deleted,
                'cutoff'  => $cutoff->toIso8601String(),
            ];
        }

        return $results;
    }

    public function retentionDays(string $table): int
    {
        $key = (string) config('platform_retention.config_key_prefix', 'retention.').$table.'_days';
        $row = DB::table('system_configurations')
            ->where('config_key', $key)
            ->where('is_active', true)
            ->orderByDesc('version')
            ->first();

        if ($row !== null) {
            $value = json_decode((string) ($row->config_value ?? ''), true);
            if (is_array($value) && isset($value['days']) && is_numeric($value['days'])) {
                return max(1, (int) $value['days']);
            }
        }

        /** @var array<string, int> $defaults */
        $defaults = config('platform_retention.tables', []);

        return max(1, (int) ($defaults[$table] ?? 30));
    }

    private function timestampColumn(string $table): string
    {
        return match ($table) {
            'message_queue'         => 'published_at',
            'event_logs'            => 'logged_at',
            'observability_metrics' => 'recorded_at',
            'trace_logs'            => 'created_at',
            'event_store'           => 'occurred_at',
            'audit_logs'            => 'occurred_at',
            default                 => 'created_at',
        };
    }
}
