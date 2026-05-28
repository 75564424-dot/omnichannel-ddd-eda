<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Repositories;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;

interface EventFeedRepositoryInterface
{
    public function save(EventFeedEntry $entry): int;

    public function existsByEventId(string $eventId): bool;

    /** @return EventFeedEntry[] */
    public function getRecent(int $limit = 50): array;

    /** @return EventFeedEntry[] */
    public function getPaginated(int $page, int $limit): array;

    public function countAll(): int;

    /** @return EventFeedEntry[] Returns entries with id > $lastId */
    public function getNewerThan(int $lastId, int $limit = 50): array;

    public function computeAverageLatencyMs(int $lastN = 100): int;

    public function countEventsInLastSeconds(int $seconds = 60): int;

    public function countReceivedSince(\DateTimeInterface $since): int;

    /**
     * @param list<string> $pathKeys nested keys under raw_payload JSON
     *
     * @return list<array{date: string, total: float}>
     */
    public function sumPayloadPathByCalendarDay(string $eventType, array $pathKeys, int $days = 14): array;

    /**
     * @param list<string> $eventTypes empty = all types in feed
     *
     * @return list<array{date: string, total: int}>
     */
    public function countEventsByCalendarDay(array $eventTypes, int $days = 14): array;
}
