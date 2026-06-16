<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;

final class EventFeedCalendarSeriesQuery
{
    /**
     * @param list<string> $pathKeys
     *
     * @return list<array{date: string, total: float}>
     */
    public function sumPayloadPathByCalendarDay(string $eventType, array $pathKeys, int $days = 14): array
    {
        $days  = max(1, min(90, $days));
        $tz    = config('app.timezone');
        $since = now()->subDays($days - 1)->startOfDay();

        $rows = EventFeedEntryModel::query()
            ->where('event_type', $eventType)
            ->where('occurred_at', '>=', $since)
            ->orderBy('occurred_at')
            ->get(['occurred_at', 'raw_payload']);

        $sums = [];
        foreach ($rows as $row) {
            $day     = $row->occurred_at->clone()->timezone($tz)->format('Y-m-d');
            $payload = $row->raw_payload ?? [];
            $value   = $payload;
            foreach ($pathKeys as $key) {
                $value = is_array($value) ? ($value[$key] ?? 0) : 0;
            }
            $sums[$day] = ($sums[$day] ?? 0.0) + (float) $value;
        }

        return $this->buildDailySeries($days, $tz, static fn (string $day) => round($sums[$day] ?? 0.0, 2));
    }

    /**
     * @param list<string> $eventTypes
     *
     * @return list<array{date: string, total: int}>
     */
    public function countEventsByCalendarDay(array $eventTypes, int $days = 14): array
    {
        $days  = max(1, min(90, $days));
        $tz    = config('app.timezone');
        $since = now()->subDays($days - 1)->startOfDay();

        $query = EventFeedEntryModel::query()
            ->where('occurred_at', '>=', $since);

        $normalizedTypes = array_values(array_unique(array_filter(array_map(
            static fn ($t) => trim((string) $t),
            $eventTypes,
        ))));

        if ($normalizedTypes !== []) {
            $query->whereIn('event_type', $normalizedTypes);
        }

        $counts = [];
        foreach ($query->get(['occurred_at']) as $row) {
            $day = $row->occurred_at->clone()->timezone($tz)->format('Y-m-d');
            $counts[$day] = ($counts[$day] ?? 0) + 1;
        }

        return $this->buildDailySeries($days, $tz, static fn (string $day) => (int) ($counts[$day] ?? 0));
    }

    /**
     * @param callable(string): float|int $valueForDay
     *
     * @return list<array{date: string, total: float|int}>
     */
    private function buildDailySeries(int $days, string $tz, callable $valueForDay): array
    {
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d        = now()->subDays($i)->timezone($tz)->format('Y-m-d');
            $series[] = [
                'date'  => $d,
                'total' => $valueForDay($d),
            ];
        }

        return $series;
    }
}
