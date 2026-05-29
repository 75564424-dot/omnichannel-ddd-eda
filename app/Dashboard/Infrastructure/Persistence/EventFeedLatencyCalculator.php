<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;
use DateTimeImmutable;

final class EventFeedLatencyCalculator
{
    public function averageMs(int $lastN = 100): int
    {
        $entries = EventFeedEntryModel::orderByDesc('id')
            ->limit($lastN)
            ->get(['occurred_at', 'received_at']);

        if ($entries->isEmpty()) {
            return 0;
        }

        $totalMs = $entries->sum(function ($e) {
            $occurred = DateTimeImmutable::createFromInterface($e->occurred_at);
            $received = DateTimeImmutable::createFromInterface($e->received_at);

            return max(0, ($received->getTimestamp() - $occurred->getTimestamp()) * 1000);
        });

        return (int) round($totalMs / $entries->count());
    }
}
