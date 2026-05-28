<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers;

use App\Dashboard\Application\UseCases\StreamLiveEventsUseCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Opens an SSE (Server-Sent Events) stream to the Dashboard frontend.
 * Uses the Last-Event-ID header to allow resumable connections.
 *
 * Client usage:
 *   const es = new EventSource('/api/dashboard/stream');
 *   es.addEventListener('event_feed', (e) => { ... });
 *
 * Authentication note: guard this endpoint in production (e.g. Sanctum token
 * passed as a query parameter, since EventSource does not support custom headers).
 */
final class EventStreamController
{
    public function __construct(
        private readonly StreamLiveEventsUseCase $streamLiveEvents,
    ) {}

    public function stream(Request $request): StreamedResponse
    {
        // Prefer Last-Event-ID header (standard SSE resume protocol)
        $lastEventId = (int) ($request->header('Last-Event-ID', 0) ?: $request->query('last_id', 0));

        return $this->streamLiveEvents->execute($lastEventId);
    }
}
