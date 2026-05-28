<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * Read-only analytics on middleware message_queue — no writes, no domain imports from Middleware BC.
 */
final class DbBusQueueAnalyticsRepository implements BusQueueAnalyticsRepositoryInterface
{
    public function countByOriginSince(DateTimeInterface $since): array
    {
        $rows = DB::table('message_queue')
            ->selectRaw('origin, COUNT(*) as c')
            ->where('published_at', '>=', $since)
            ->groupBy('origin')
            ->orderByDesc('c')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $label = trim((string) ($row->origin ?? '')) ?: '—';
            $out[$label] = (int) $row->c;
        }

        return $out;
    }

    public function countByConsumerSince(DateTimeInterface $since): array
    {
        $rows = DB::table('message_queue')
            ->where('published_at', '>=', $since)
            ->whereNotNull('target_consumers')
            ->get(['target_consumers']);

        $counts = [];
        foreach ($rows as $row) {
            $raw = $row->target_consumers;
            $list = is_string($raw) ? json_decode($raw, true) : $raw;
            if (! is_array($list) || $list === []) {
                $label = '(sin consumidor configurado)';
                $counts[$label] = ($counts[$label] ?? 0) + 1;

                continue;
            }
            foreach ($list as $consumer) {
                $name = trim((string) $consumer) ?: '—';
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }

        arsort($counts);

        return $counts;
    }

    public function countPublishedSince(DateTimeInterface $since): int
    {
        return (int) DB::table('message_queue')
            ->where('published_at', '>=', $since)
            ->count();
    }
}
