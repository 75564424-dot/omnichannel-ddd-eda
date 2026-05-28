<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;
use App\Middleware\Domain\Repositories\BusMetricsRepositoryInterface;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Middleware\Domain\ValueObjects\ErrorRate;
use App\Middleware\Domain\ValueObjects\LatencyMs;
use App\Middleware\Domain\ValueObjects\ThroughputEps;

/**
 * Computes live bus metrics from queue tracking data.
 * Pure infrastructure concern — no business logic.
 */
final class BusMetricsService
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface  $queueEntryRepository,
        private readonly DeadLetterRepositoryInterface  $deadLetterRepository,
        private readonly BusMetricsRepositoryInterface  $metricsRepository,
    ) {}

    public function computeAndSnapshot(): BusMetricsSnapshot
    {
        $windowSeconds   = 60;
        $totalInWindow   = $this->queueEntryRepository->countTotal($windowSeconds);
        $failedInWindow  = $this->queueEntryRepository->countByStatus('FALLIDO', $windowSeconds);
        $avgLatencyMs    = $this->queueEntryRepository->computeAverageProcessingTimeMs();
        $deadLetterCount = $this->deadLetterRepository->countUnresolved();

        $eps       = ThroughputEps::of(max(0, (int) round($totalInWindow / $windowSeconds)));
        $latency   = LatencyMs::of($avgLatencyMs);
        $errorRate = ErrorRate::compute($failedInWindow, max(1, $totalInWindow));

        // Thresholds come from config — Application layer reads config, Domain stays pure
        $thresholds = config('eventbus.thresholds', []);
        $status = BusStatus::evaluate(
            errorRate:                   $errorRate,
            eps:                         $eps,
            latency:                     $latency,
            deadLetterCount:             $deadLetterCount,
            criticalErrorRateThreshold:  (float) ($thresholds['critical_error_rate'] ?? 10.0),
            highLoadEpsThreshold:        (int)   ($thresholds['high_load_eps']       ?? 100),
            criticalLatencyMs:           (int)   ($thresholds['critical_latency_ms'] ?? 2000),
            deadLetterAlertThreshold:    (int)   ($thresholds['dead_letter_alert']   ?? 10),
        );

        $snapshot = new BusMetricsSnapshot(
            latencyMs:        $latency,
            eventsPerSecond:  $eps,
            errorRate:        $errorRate,
            deadLettersCount: $deadLetterCount,
            busStatus:        $status,
            recordedAt:       now()->toDateTimeString(),
        );

        $this->metricsRepository->saveSnapshot($snapshot);

        return $snapshot;
    }
}
