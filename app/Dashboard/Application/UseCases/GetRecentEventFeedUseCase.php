<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Application\DTOs\EventFeedEntryDTO;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;

final class GetRecentEventFeedUseCase
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $feedRepository,
    ) {}

    /** @return EventFeedEntryDTO[] */
    public function execute(int $limit = 50): array
    {
        $limit   = max(1, min(200, $limit));
        $entries = $this->feedRepository->getRecent($limit);

        return array_map(fn ($e) => EventFeedEntryDTO::fromReadModel($e), $entries);
    }

    /** @return EventFeedEntryDTO[] */
    public function executePaginated(int $page, int $limit): array
    {
        $limit   = max(1, min(200, $limit));
        $entries = $this->feedRepository->getPaginated($page, $limit);

        return array_map(fn ($e) => EventFeedEntryDTO::fromReadModel($e), $entries);
    }

    public function countAll(): int
    {
        return $this->feedRepository->countAll();
    }
}
