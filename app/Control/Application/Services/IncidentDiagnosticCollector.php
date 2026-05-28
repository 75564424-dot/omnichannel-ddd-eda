<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Middleware\Application\DTOs\BusMetricsDTO;
use App\Middleware\Application\Services\BusHealthService;
use App\Monitoring\Application\Services\AlertEvaluationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Captures a point-in-time diagnostic bundle when a client submits a support report.
 */
final class IncidentDiagnosticCollector
{
    public function __construct(
        private readonly BusHealthService $busHealth,
        private readonly AlertEvaluationService $alerts,
        private readonly GetSystemNodeStatusUseCase $nodeStatus,
    ) {}

    /**
     * @param array<string, mixed> $clientContext
     *
     * @return array<string, mixed>
     */
    public function collect(array $clientContext = []): array
    {
        $snapshot = $this->busHealth->getLatestSnapshot();
        $metrics = BusMetricsDTO::fromSnapshot($snapshot)->toArray();
        $activeAlerts = array_map(
            static fn ($a) => $a->toArray(),
            $this->alerts->evaluate(),
        );

        $nodes = $this->nodeStatus->execute()->toArray();

        return [
            'captured_at' => now()->toIso8601String(),
            'environment' => [
                'app_url'     => config('app.url'),
                'client_slug' => config('platform.client_slug'),
                'app_env'     => config('app.env'),
            ],
            'bus' => [
                'status'            => $metrics['bus_status'] ?? null,
                'latency_ms'        => $metrics['latency_ms'] ?? null,
                'error_rate_percent'=> $metrics['error_rate'] ?? null,
                'throughput_eps'    => $metrics['events_per_second'] ?? null,
                'dead_letters'      => $metrics['dead_letters'] ?? null,
            ],
            'active_alerts'   => $activeAlerts,
            'nodes'           => $nodes['status_by_node'] ?? [],
            'nodes_updated_at'=> $nodes['last_updated'] ?? null,
            'recent_failures' => $this->recentFailures(),
            'client_context'  => $clientContext,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function recentFailures(): array
    {
        $items = [];

        if (Schema::hasTable('dead_letter_queue')) {
            $dlq = DB::table('dead_letter_queue')
                ->whereNull('resolved_at')
                ->orderByDesc('failed_at')
                ->limit(8)
                ->get(['event_uuid', 'event_type', 'origin', 'failure_reason', 'failed_at']);

            foreach ($dlq as $row) {
                $items[] = [
                    'source'  => 'dead_letter_queue',
                    'type'    => $row->event_type,
                    'origin'  => $row->origin,
                    'detail'  => $row->failure_reason,
                    'at'      => $row->failed_at,
                    'event_uuid' => $row->event_uuid,
                ];
            }
        }

        if (Schema::hasTable('event_logs')) {
            $logs = DB::table('event_logs')
                ->whereIn('status', ['failed', 'error', 'FAILED', 'ERROR'])
                ->orderByDesc('logged_at')
                ->limit(8)
                ->get(['event_uuid', 'event_type', 'origin', 'status', 'summary', 'logged_at']);

            foreach ($logs as $row) {
                $items[] = [
                    'source'  => 'event_logs',
                    'type'    => $row->event_type,
                    'origin'  => $row->origin,
                    'detail'  => $row->summary,
                    'status'  => $row->status,
                    'at'      => $row->logged_at,
                    'event_uuid' => $row->event_uuid,
                ];
            }
        }

        return array_slice($items, 0, 12);
    }
}
