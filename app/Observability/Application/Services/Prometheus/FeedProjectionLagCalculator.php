<?php

declare(strict_types=1);

namespace App\Observability\Application\Services\Prometheus;

use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;
use Illuminate\Support\Facades\Schema;

/**
 * ACL: average feed projection lag from Dashboard read model.
 */
final class FeedProjectionLagCalculator
{
    public function averageLagMs(): int
    {
        if (! Schema::hasTable('event_feed_projections')) {
            return 0;
        }

        $entries = EventFeedEntryModel::orderByDesc('id')
            ->limit(100)
            ->get(['occurred_at', 'received_at']);

        if ($entries->isEmpty()) {
            return 0;
        }

        $totalMs = $entries->sum(function ($e) {
            return max(0, ($e->received_at->getTimestamp() - $e->occurred_at->getTimestamp()) * 1000);
        });

        return (int) round($totalMs / $entries->count());
    }
}
