<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Jobs;

use App\Middleware\Application\Services\EventProcessingService;
use App\Middleware\Domain\Repositories\OutboxRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Relays pending outbox rows to the runtime EventBusPort (Plan_Middleware Fase 3).
 */
final class RelayOutboxJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $batchSize = 50,
    ) {}

    public function handle(
        OutboxRepositoryInterface $outbox,
        EventProcessingService $processing,
    ): void {
        foreach ($outbox->claimPending($this->batchSize) as $row) {
            try {
                $processing->publishToBus(
                    $row['event_uuid'],
                    $row['event_type'],
                    $row['payload'],
                    $row['origin'],
                );

                $outbox->markPublished($row['id']);
            } catch (Throwable $e) {
                $outbox->markFailed($row['id']);
                Log::error('[EventBus][Outbox] relay failed', [
                    'outbox_id'  => $row['id'],
                    'event_id'   => $row['event_uuid'],
                    'event_type' => $row['event_type'],
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
