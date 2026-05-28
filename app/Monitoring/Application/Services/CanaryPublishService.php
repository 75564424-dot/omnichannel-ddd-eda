<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use App\Middleware\Application\Services\EventPublisherService;
use App\Observability\Application\Services\SliMetricsRecorder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Throwable;

/**
 * Synthetic canary publish probe (Plan_Monitoreo Fase 3).
 */
final class CanaryPublishService
{
    public const CACHE_KEY_LAST_SUCCESS = 'platform.monitoring.canary_last_success';

    public function __construct(
        private readonly EventPublisherService $publisher,
        private readonly SliMetricsRecorder $sliMetrics,
    ) {}

    public function run(): bool
    {
        if (! config('platform_monitoring.canary.enabled', true)) {
            return true;
        }

        $eventId   = Uuid::uuid4()->toString();
        $occurred  = now()->toIso8601String();
        $eventType = (string) config('platform_monitoring.canary.event_type', 'Platform.Monitoring.Canary');

        try {
            $result = $this->publisher->publish([
                'event_id'    => $eventId,
                'event_type'  => $eventType,
                'occurred_at' => $occurred,
                'origin'      => 'MonitoringCanary',
                'payload'     => [
                    'event_id'    => $eventId,
                    'event'       => $eventType,
                    'occurred_at' => $occurred,
                    'probe'       => 'canary',
                ],
            ]);

            $row = DB::table('message_queue')->where('event_uuid', $eventId)->first();
            $ok  = $row !== null && in_array(strtolower((string) ($row->status ?? '')), ['completed', 'processed', 'procesado'], true);

            if ($ok) {
                Cache::put(self::CACHE_KEY_LAST_SUCCESS, now()->timestamp, now()->addDay());
                $this->sliMetrics->record('canary_publish_success', 1.0);
            } else {
                $this->sliMetrics->record('canary_publish_success', 0.0);
            }

            return $ok;
        } catch (Throwable) {
            $this->sliMetrics->record('canary_publish_success', 0.0);

            return false;
        }
    }

    public function lastSuccessAgeSeconds(): int
    {
        $ts = Cache::get(self::CACHE_KEY_LAST_SUCCESS);
        if ($ts === null) {
            return -1;
        }

        return max(0, now()->timestamp - (int) $ts);
    }
}
