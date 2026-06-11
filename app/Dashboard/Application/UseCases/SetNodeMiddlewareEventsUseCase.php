<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Domain\DashboardKnownNodes;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use InvalidArgumentException;

final class SetNodeMiddlewareEventsUseCase
{
    public function __construct(
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
        private readonly DashboardKnownNodes $knownNodes,
    ) {}

    public function execute(string $nodeKey, bool $enabled): void
    {
        if (! $this->knownNodes->exists($nodeKey)) {
            throw new InvalidArgumentException('Unknown node');
        }

        $this->nodeStatusRepository->setMiddlewareEventsEnabled($nodeKey, $enabled);
    }
}
