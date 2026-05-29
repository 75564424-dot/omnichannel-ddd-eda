<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Shared\Logging\PlatformStructuredLogger;
use Throwable;

/**
 * Processes simulation events that were left PENDING during the publish loop.
 */
final class SimulationQueueDrainer
{
    public function __construct(
        private readonly EventProcessingService $processing,
        private readonly PlatformStructuredLogger $logger,
    ) {}

    /**
     * @param list<string> $eventIds
     */
    public function drain(array $eventIds): void
    {
        foreach ($eventIds as $eventId) {
            if ($eventId === '') {
                continue;
            }

            try {
                $this->processing->processQueuedEvent($eventId);
            } catch (Throwable $e) {
                $this->logger->warning('Simulation queue drain failed for event', [
                    'event_uuid' => $eventId,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
