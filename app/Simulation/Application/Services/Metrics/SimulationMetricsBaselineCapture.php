<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Metrics;

use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Infrastructure\Models\QueueEntryModel;
use Illuminate\Support\Facades\Schema;

final class SimulationMetricsBaselineCapture
{
    public function __construct(
        private readonly BusHealthService $busHealth,
    ) {}

    /** @return array<string, mixed> */
    public function captureEnvironmentBaseline(): array
    {
        $snapshot = $this->busHealth->getLatestSnapshot();

        return [
            'captured_at'        => now()->toDateTimeString(),
            'bus_status'         => $snapshot->busStatus->value(),
            'latency_ms'         => $snapshot->latencyMs->value(),
            'events_per_second'  => $snapshot->eventsPerSecond->value(),
            'error_rate_percent' => round($snapshot->errorRate->value(), 2),
            'dead_letters'       => $snapshot->deadLettersCount,
            'queue_pending'      => $this->countQueueByStatuses(['pending', 'PENDING', 'processing', 'PROCESSING']),
            'peak_memory_mb'     => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];
    }

    /** @param list<string> $statuses */
    private function countQueueByStatuses(array $statuses): int
    {
        if (! Schema::hasTable('message_queue')) {
            return 0;
        }

        return (int) QueueEntryModel::query()->whereIn('status', $statuses)->count();
    }
}
