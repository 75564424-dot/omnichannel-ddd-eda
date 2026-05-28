<?php

declare(strict_types=1);

namespace App\Dashboard\Listeners;

use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\Repositories\MiddlewareBusMetricsRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\StreamStatus;
use App\Shared\EventBus\PlatformWildcardPayload;
use Illuminate\Support\Facades\Log;

/**
 * Refreshes MiddlewareBusMetrics snapshots from feed-derived signals (EPS, latency). Synchronous for wildcard registration.
 */
final class MiddlewareMetricsListener
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $feedRepository,
        private readonly MiddlewareBusMetricsRepositoryInterface $busMetricsRepository,
    ) {}

    /**
     * @param  string|array<string, mixed>  $first
     * @param  array<int, mixed>|null  $second
     */
    public function handle(mixed $first, mixed $second = null): void
    {
        if (is_string($first) && ! PlatformWildcardPayload::shouldObserveWildcardEvent($first)) {
            return;
        }

        [, $payload] = PlatformWildcardPayload::parse($first, $second);
        if (empty($payload['event_id'])) {
            return;
        }

        $latencyMs = $this->feedRepository->computeAverageLatencyMs(100);
        $eps       = $this->feedRepository->countEventsInLastSeconds(60);
        $status    = StreamStatus::fromMetrics($eps, 0);

        $this->busMetricsRepository->saveSnapshot($latencyMs, $eps, 0, $status->value());

        Log::debug('Dashboard: MiddlewareBusMetrics snapshot updated', [
            'latency_ms'    => $latencyMs,
            'eps'           => $eps,
            'stream_status' => $status->value(),
        ]);
    }
}
