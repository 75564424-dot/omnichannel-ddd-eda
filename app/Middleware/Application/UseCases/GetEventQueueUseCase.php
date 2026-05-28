<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\DTOs\QueueEntryDTO;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;

final class GetEventQueueUseCase
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntryRepository,
    ) {}

    /**
     * @return QueueEntryDTO[]
     */
    public function execute(int $limit = 50): array
    {
        $entries = $this->queueEntryRepository->getRecent($limit);

        return array_map(fn ($e) => QueueEntryDTO::fromEntity($e), $entries);
    }

    /**
     * @return QueueEntryDTO[]
     */
    public function executePaginated(int $page, int $limit): array
    {
        $entries = $this->queueEntryRepository->getPaginated($page, $limit);

        return array_map(fn ($e) => QueueEntryDTO::fromEntity($e), $entries);
    }

    public function countAll(): int
    {
        return $this->queueEntryRepository->countAll();
    }
}
