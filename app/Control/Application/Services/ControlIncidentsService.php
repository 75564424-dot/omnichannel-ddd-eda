<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Monitoring\Application\Services\AlertEvaluationService;

/**
 * Unified incidents view: system alerts + client reports + platform metrics.
 */
final class ControlIncidentsService
{
    public function __construct(
        private readonly AlertEvaluationService $alerts,
        private readonly ControlMiddlewareService $middleware,
        private readonly ClientIncidentReportService $clientReports,
    ) {}

    /** @return array<string, mixed> */
    public function buildDashboard(): array
    {
        $rawAlerts = $this->alerts->evaluate();
        $alerts = array_map(static function ($a) {
            $row = $a->toArray();
            $row['type'] = 'system_alert';
            $row['category'] = self::alertCategory($row['name']);
            $row['evaluated_at'] = now()->toDateTimeString();
            $current = (float) ($row['current_value'] ?? 0);
            $threshold = (float) ($row['threshold'] ?? 1);
            $row['over_threshold_pct'] = $threshold > 0
                ? round((($current - $threshold) / $threshold) * 100, 1)
                : 0;

            return $row;
        }, $rawAlerts);

        $middleware = $this->middleware->snapshot();
        $metrics = is_array($middleware['metrics'] ?? null) ? $middleware['metrics'] : [];
        $clientReports = $this->clientReports->listForControl();
        $reportSummary = $this->clientReports->summaryCounts();

        $unified = $this->mergeUnifiedTimeline($alerts, $clientReports);

        return [
            'metrics' => [
                'bus_status'       => $metrics['bus_status'] ?? 'UNKNOWN',
                'latency_ms'       => (int) ($metrics['latency_ms'] ?? 0),
                'error_rate'       => (float) ($metrics['error_rate'] ?? $metrics['error_rate_percent'] ?? 0),
                'throughput_eps'   => (int) ($metrics['events_per_second'] ?? 0),
                'queue_depth'      => (int) (($middleware['queues']['depth'] ?? 0)),
                'dead_letters'     => (int) ($metrics['dead_letters'] ?? $metrics['dead_letters_count'] ?? 0),
                'active_alerts'    => count($alerts),
                'open_client_reports' => $reportSummary['open'],
                'reports_last_24h' => $reportSummary['last_24h'],
            ],
            'alerts'           => $alerts,
            'categories'       => $this->categorizeAlerts($alerts),
            'client_reports'   => $clientReports,
            'client_summary'   => $reportSummary,
            'unified_timeline' => $unified,
        ];
    }

    /** @param list<array<string, mixed>> $alerts */
    private static function alertCategory(string $name): string
    {
        return match ($name) {
            'BusStopped'     => 'caídas',
            'HighLatency', 'HighErrorRate' => 'degradaciones',
            'DLQBacklog', 'QueueBacklog' => 'integraciones_rotas',
            'DiskSpace'      => 'recursos',
            default          => 'otros',
        };
    }

    /**
     * @param list<array<string, mixed>> $alerts
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function categorizeAlerts(array $alerts): array
    {
        $cats = [
            'caídas'              => [],
            'degradaciones'       => [],
            'integraciones_rotas' => [],
            'recursos'            => [],
            'otros'               => [],
        ];

        foreach ($alerts as $alert) {
            $key = $alert['category'] ?? 'otros';
            if (! isset($cats[$key])) {
                $key = 'otros';
            }
            $cats[$key][] = $alert;
        }

        return $cats;
    }

    /**
     * @param list<array<string, mixed>> $alerts
     * @param list<array<string, mixed>> $reports
     *
     * @return list<array<string, mixed>>
     */
    private function mergeUnifiedTimeline(array $alerts, array $reports): array
    {
        $items = [];

        foreach ($alerts as $alert) {
            $items[] = [
                'id'           => 'alert-'.$alert['name'],
                'type'         => 'system_alert',
                'sort_at'      => $alert['evaluated_at'] ?? now()->toDateTimeString(),
                'client_label' => 'Sistema (monitoreo)',
                'title'        => $alert['name'],
                'problem'      => $alert['message'],
                'severity'     => $alert['severity'],
                'status'       => 'active',
                'detail'       => $alert,
            ];
        }

        foreach ($reports as $report) {
            $log = is_array($report['diagnostic_log'] ?? null) ? $report['diagnostic_log'] : [];
            $isSimulation = ($log['source'] ?? '') === 'simulation_failure';

            $items[] = [
                'id'           => $report['id'],
                'type'         => $isSimulation ? 'simulation_failure' : 'client_report',
                'sort_at'      => $report['created_at'],
                'client_label' => $report['client_label'],
                'title'        => $report['subject'],
                'problem'      => $report['description'],
                'severity'     => strtoupper($report['severity']),
                'status'       => $report['status'],
                'detail'       => $report,
                'simulation_run_id' => $isSimulation ? ($log['run_id'] ?? null) : null,
            ];
        }

        usort($items, static fn ($a, $b) => strcmp((string) $b['sort_at'], (string) $a['sort_at']));

        return $items;
    }
}
