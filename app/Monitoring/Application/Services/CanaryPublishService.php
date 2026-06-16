<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services;

use App\Middleware\Application\Services\EventPublisherService;
use App\Monitoring\Application\Services\Canary\CanaryProbeEnvelopeFactory;
use App\Monitoring\Application\Services\Canary\CanaryQueueCompletionVerifier;
use App\Monitoring\Application\Services\Canary\CanarySuccessTracker;
use App\Observability\Application\Services\SliMetricsRecorder;
use Throwable;

/**
 * Synthetic canary publish probe (Plan_Monitoreo Fase 3).
 */
final class CanaryPublishService
{
    public function __construct(
        private readonly EventPublisherService $publisher,
        private readonly CanaryProbeEnvelopeFactory $envelopeFactory,
        private readonly CanaryQueueCompletionVerifier $completionVerifier,
        private readonly CanarySuccessTracker $successTracker,
        private readonly SliMetricsRecorder $sliMetrics,
    ) {}

    public function run(): bool
    {
        if (! config('platform_monitoring.canary.enabled', true)) {
            return true;
        }

        $envelope = $this->envelopeFactory->build();
        $eventId  = (string) $envelope['event_id'];

        try {
            $this->publisher->publish($envelope);
            $ok = $this->completionVerifier->isCompleted($eventId);

            if ($ok) {
                $this->successTracker->markSuccess();
            }

            $this->sliMetrics->record('canary_publish_success', $ok ? 1.0 : 0.0);

            return $ok;
        } catch (Throwable) {
            $this->sliMetrics->record('canary_publish_success', 0.0);

            return false;
        }
    }

    public function lastSuccessAgeSeconds(): int
    {
        return $this->successTracker->lastSuccessAgeSeconds();
    }
}
