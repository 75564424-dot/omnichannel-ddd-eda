<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\Entities\StoredEvent;

/**
 * Projects canonical events into event_logs on ingest — delegates to EventLogService.
 */
final class EventLogProjector
{
    public function __construct(
        private readonly EventLogService $eventLogs,
    ) {}

    public function projectReceived(StoredEvent $stored): int
    {
        return $this->eventLogs->recordReceived($stored);
    }
}
