<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics;

use App\Middleware\Infrastructure\Models\QueueEntryModel;
use App\Shared\Persistence\MessageQueueStatusMapper;
use Illuminate\Database\DatabaseManager;

final class SimulationQueueMetricsAnalyzer
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {}
    /**
     * @param list<string> $eventIds
     *
     * @return array<string, int|float>
     */
    public function queueStatsForEvents(array $eventIds): array
    {
        if ($eventIds === [] || ! $this->db->getSchemaBuilder()->hasTable('message_queue')) {
            return [
                'processed'          => 0,
                'failed'             => 0,
                'pending'            => 0,
                'dead_lettered'      => 0,
                'avg_processing_ms'  => 0,
                'p95_processing_ms'  => 0,
                'max_processing_ms'  => 0,
            ];
        }

        $rows = QueueEntryModel::query()
            ->whereIn('event_uuid', $eventIds)
            ->get(['status', 'processing_time_ms']);

        $latencies = $rows->pluck('processing_time_ms')->filter(fn ($v) => $v !== null)->map(fn ($v) => (int) $v)->sort()->values();

        $processed = 0;
        $failed = 0;
        $pending = 0;
        $dead = 0;

        foreach ($rows as $row) {
            $normalized = MessageQueueStatusMapper::fromDb((string) $row->status);
            match ($normalized) {
                'PROCESADO', 'SUCCESS' => $processed++,
                'FALLIDO', 'FAILED' => $failed++,
                'dead_lettered' => $dead++,
                default => $pending++,
            };
        }

        return [
            'processed'         => $processed,
            'failed'            => $failed,
            'pending'           => $pending,
            'dead_lettered'     => $dead,
            'avg_processing_ms' => $latencies->isEmpty() ? 0 : (int) round($latencies->avg()),
            'p95_processing_ms' => $this->percentile($latencies->all(), 95),
            'max_processing_ms' => $latencies->isEmpty() ? 0 : (int) $latencies->max(),
        ];
    }

    /**
     * @param list<string> $eventIds
     *
     * @return array{avg_interval_ms: int, max_interval_ms: int}
     */
    public function interEventTiming(array $eventIds, int $targetPerMinute): array
    {
        if (count($eventIds) < 2 || ! $this->db->getSchemaBuilder()->hasTable('message_queue')) {
            $targetMs = (int) round(60_000 / max(1, $targetPerMinute));

            return ['avg_interval_ms' => $targetMs, 'max_interval_ms' => $targetMs];
        }

        $times = QueueEntryModel::query()
            ->whereIn('event_uuid', $eventIds)
            ->orderBy('published_at')
            ->pluck('published_at')
            ->map(fn ($t) => $t?->getTimestamp() ?? 0)
            ->filter(fn ($t) => $t > 0)
            ->values()
            ->all();

        $gaps = [];
        for ($i = 1, $iMax = count($times); $i < $iMax; $i++) {
            $gaps[] = ($times[$i] - $times[$i - 1]) * 1000;
        }

        if ($gaps === []) {
            $targetMs = (int) round(60_000 / max(1, $targetPerMinute));

            return ['avg_interval_ms' => $targetMs, 'max_interval_ms' => $targetMs];
        }

        return [
            'avg_interval_ms' => (int) round(array_sum($gaps) / count($gaps)),
            'max_interval_ms' => (int) max($gaps),
        ];
    }

    /** @param list<int> $values */
    private function percentile(array $values, int $percentile): int
    {
        if ($values === []) {
            return 0;
        }
        sort($values);
        $index = (int) ceil(($percentile / 100) * count($values)) - 1;

        return $values[max(0, min($index, count($values) - 1))];
    }
}
