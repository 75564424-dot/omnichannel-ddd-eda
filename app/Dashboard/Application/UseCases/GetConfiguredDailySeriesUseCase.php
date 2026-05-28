<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;

/**
 * Builds a daily numeric series from the event feed using {@see config('dashboard.daily_series')}.
 * Returns an empty series when the host has not registered a definition.
 */
final class GetConfiguredDailySeriesUseCase
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $feedRepository,
    ) {}

    /**
     * @return list<array{date: string, total: float}>
     */
    public function execute(int $days = 14): array
    {
        $spec = config('dashboard.daily_series');
        if (! is_array($spec) || empty($spec['event_type']) || empty($spec['payload_path'])) {
            return [];
        }

        /** @var list<string> $path */
        $path = array_values(array_map(static fn ($p) => (string) $p, $spec['payload_path']));

        return $this->feedRepository->sumPayloadPathByCalendarDay((string) $spec['event_type'], $path, $days);
    }
}
