<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\DTOs\DeadLetterDTO;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;

final class GetDeadLetterQueueUseCase
{
    public function __construct(
        private readonly DeadLetterRepositoryInterface $deadLetterRepository,
    ) {}

    /**
     * Syncs from Laravel failed_jobs, then returns all unresolved dead letters.
     *
     * @return DeadLetterDTO[]
     */
    public function execute(): array
    {
        $this->deadLetterRepository->syncFromFailedJobs();

        $entries = $this->deadLetterRepository->findUnresolved();
        return array_map(fn($e) => DeadLetterDTO::fromEntity($e), $entries);
    }
}
