<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Repositories\RetryAttemptRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class EloquentRetryAttemptRepository implements RetryAttemptRepositoryInterface
{
    public function recordAttempt(
        int $messageQueueId,
        string $eventUuid,
        int $attemptNumber,
        string $status,
        ?string $errorMessage = null,
    ): void {
        if (! \Illuminate\Support\Facades\Schema::hasTable('retries')) {
            return;
        }

        DB::table('retries')->insert([
            'id'               => Uuid::uuid4()->toString(),
            'message_queue_id' => $messageQueueId,
            'event_uuid'       => $eventUuid,
            'attempt_number'   => $attemptNumber,
            'scheduled_at'     => now(),
            'executed_at'      => now(),
            'status'           => $status,
            'error_message'    => $errorMessage,
            'created_at'       => now(),
        ]);
    }
}
