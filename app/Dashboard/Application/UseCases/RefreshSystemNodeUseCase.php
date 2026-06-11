<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Domain\DashboardKnownNodes;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\NodeStatus;
use App\Events\ControlPlaneNodeRefreshRequested;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;

final class RefreshSystemNodeUseCase
{
    public function __construct(
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
        private readonly DashboardKnownNodes $knownNodes,
        private readonly Dispatcher $events,
    ) {}

    public function execute(string $nodeKey): void
    {
        if (! $this->knownNodes->exists($nodeKey)) {
            throw new InvalidArgumentException('Unknown node');
        }

        $this->nodeStatusRepository->setMiddlewareEventsEnabled($nodeKey, true);
        $this->nodeStatusRepository->setStatus($nodeKey, NodeStatus::ONLINE);

        $this->events->dispatch(new ControlPlaneNodeRefreshRequested($nodeKey));
    }
}
