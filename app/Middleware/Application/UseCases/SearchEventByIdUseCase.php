<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\DTOs\QueueEntryDTO;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;

final class SearchEventByIdUseCase
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntryRepository,
    ) {}

    public function execute(string $eventId): ?QueueEntryDTO
    {
        $entry = $this->queueEntryRepository->findByEventId($eventId);
        if ($entry === null) return null;
        return QueueEntryDTO::fromEntity($entry);
    }
}
