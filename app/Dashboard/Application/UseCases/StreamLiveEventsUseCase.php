<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Observability\Application\Services\StreamConnectionTracker;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Implements Server-Sent Events (SSE) streaming for the Dashboard.
 * Uses DB polling — no external broker required (plan §8.5 recommends SSE).
 *
 * The client sends `Last-Event-ID` header to resume from a specific position.
 * Each poll reads entries newer than the last known ID and pushes them to the stream.
 * Poll interval: 2 seconds — provides near-real-time updates.
 */
final class StreamLiveEventsUseCase
{
    private const POLL_INTERVAL_SECONDS = 2;
    private const MAX_STREAM_SECONDS    = 300; // 5 minutes — client should reconnect after this

    public function __construct(
        private readonly EventFeedRepositoryInterface $feedRepository,
        private readonly StreamConnectionTracker $streamConnections,
    ) {}

    public function execute(int $lastEventId = 0): StreamedResponse
    {
        $lastId  = $lastEventId;
        $started = time();

        return new StreamedResponse(function () use (&$lastId, $started) {
            $this->streamConnections->increment();

            try {
                // SSE handshake
                echo ": connected\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                while (! connection_aborted()) {
                    if ((time() - $started) > self::MAX_STREAM_SECONDS) {
                        echo "event: reconnect\ndata: {\"reason\":\"max_duration_reached\"}\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                        break;
                    }

                    $newEntries = $this->feedRepository->getNewerThan($lastId, 20);

                    foreach ($newEntries as $entry) {
                        $data = json_encode([
                            'id'          => $entry->id,
                            'event_type'  => $entry->eventType,
                            'origin'      => $entry->origin->value(),
                            'impact'      => $entry->impact->value(),
                            'status'      => $entry->status,
                            'occurred_at' => $entry->occurredAt->format('Y-m-d H:i:s'),
                            'received_at' => $entry->receivedAt->format('Y-m-d H:i:s'),
                            'latency_ms'  => $entry->latencyMs(),
                        ]);

                        echo "id: {$entry->id}\n";
                        echo "event: event_feed\n";
                        echo "data: {$data}\n\n";

                        $lastId = max($lastId, $entry->id);
                    }

                    // Heartbeat to keep the connection alive (every poll)
                    echo ": heartbeat\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();

                    sleep(self::POLL_INTERVAL_SECONDS);
                }
            } finally {
                $this->streamConnections->decrement();
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream; charset=UTF-8',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
