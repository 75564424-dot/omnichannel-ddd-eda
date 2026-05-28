<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Jobs;

use App\Middleware\Application\Services\EventProcessingService;
use App\Middleware\Domain\ValueObjects\RetryPolicy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Async event processing with Laravel retry/backoff (Plan_Resiliencia Fase 2).
 */
final class ProcessEventJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout;

    public function __construct(
        public readonly string $eventId,
        public readonly string $eventType,
        public readonly array $payload,
        public readonly string $origin,
    ) {
        $this->timeout = max(5, (int) config('eventbus.resilience.processing_timeout', 30));
    }

    public function tries(): int
    {
        return RetryPolicy::fromConfig()->maxAttempts;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return RetryPolicy::fromConfig()->backoffSeconds;
    }

    public function handle(EventProcessingService $processing): void
    {
        $processing->executeAttempt(
            $this->eventId,
            $this->eventType,
            $this->payload,
            $this->origin,
            $this->attempts(),
        );
    }

    public function failed(?Throwable $exception): void
    {
        app(EventProcessingService::class)->finalizeDeadLetter(
            $this->eventId,
            $this->eventType,
            $this->payload,
            $this->origin,
            $exception,
        );
    }
}
