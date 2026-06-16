<?php

declare(strict_types=1);

namespace App\Observability\Application\Services\Prometheus;

use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Monitoring\Application\Services\CanaryPublishService;
use App\Monitoring\Application\Services\DatabaseCapacityChecker;
use App\Monitoring\Application\Services\QueueDepthChecker;
use App\Observability\Application\Services\StreamConnectionTracker;
use App\Shared\Persistence\BusStatusMetricMapper;

final class PrometheusGaugeCollector
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queue,
        private readonly DeadLetterRepositoryInterface $deadLetters,
        private readonly StreamConnectionTracker $streamConnections,
        private readonly BusHealthService $busHealth,
        private readonly DatabaseCapacityChecker $databaseCapacity,
        private readonly QueueDepthChecker $queueDepth,
        private readonly CanaryPublishService $canary,
        private readonly FeedProjectionLagCalculator $feedLag,
    ) {}

    public function collect(): PrometheusGaugeSnapshot
    {
        $snapshot = $this->busHealth->getLatestSnapshot();

        return new PrometheusGaugeSnapshot(
            publishedTotal: $this->queue->countTotal(3600),
            processingLatencyMs: $this->queue->computeAverageProcessingTimeMs(),
            errorRatePercent: $snapshot->errorRate->value(),
            streamStatus: BusStatusMetricMapper::toNumeric($snapshot->busStatus->value()),
            dlqUnresolved: $this->deadLetters->countUnresolved(),
            feedProjectionLagMs: $this->feedLag->averageLagMs(),
            sseActiveConnections: $this->streamConnections->activeCount(),
            databaseUsagePercent: $this->databaseCapacity->usagePercent(),
            queueJobsPending: $this->queueDepth->totalPending(),
            canaryLastSuccessAgeSeconds: $this->canary->lastSuccessAgeSeconds(),
        );
    }
}
