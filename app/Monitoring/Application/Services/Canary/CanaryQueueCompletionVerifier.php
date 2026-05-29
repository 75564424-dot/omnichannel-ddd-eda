<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services\Canary;

use Illuminate\Support\Facades\DB;

final class CanaryQueueCompletionVerifier
{
    public function isCompleted(string $eventId): bool
    {
        $row = DB::table('message_queue')->where('event_uuid', $eventId)->first();

        return $row !== null
            && in_array(strtolower((string) ($row->status ?? '')), ['completed', 'processed', 'procesado'], true);
    }
}
