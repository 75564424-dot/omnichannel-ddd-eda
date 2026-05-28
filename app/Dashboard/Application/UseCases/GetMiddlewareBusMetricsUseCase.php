<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Application\DTOs\MiddlewareBusMetricsDTO;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\Repositories\MiddlewareBusMetricsRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\StreamStatus;
use App\Dashboard\Domain\ReadModels\MiddlewareBusMetrics;

final class GetMiddlewareBusMetricsUseCase
{
    public function __construct(
        private readonly MiddlewareBusMetricsRepositoryInterface $busMetricsRepository,
        private readonly EventFeedRepositoryInterface            $feedRepository,
    ) {}

    /**
     * Returns middleware bus metrics.
     * If no heartbeat snapshot exists, computes metrics from the event feed.
     */
    public function execute(): MiddlewareBusMetricsDTO
    {
        $latest = $this->busMetricsRepository->getLatest();

        if ($latest !== null) {
            return MiddlewareBusMetricsDTO::fromReadModel($latest);
        }

        // Compute from feed when no heartbeat snapshot is available
        $latencyMs  = $this->feedRepository->computeAverageLatencyMs(100);
        $eps        = $this->feedRepository->countEventsInLastSeconds(60);
        $streamStatus = StreamStatus::fromMetrics($eps, 0);

        $computed = new MiddlewareBusMetrics(
            latencyMs:         $latencyMs,
            processingRateEps: $eps,
            queueSize:         0,
            streamStatus:      $streamStatus,
            recordedAt:        now()->toDateTimeString(),
        );

        return MiddlewareBusMetricsDTO::fromReadModel($computed);
    }
}
