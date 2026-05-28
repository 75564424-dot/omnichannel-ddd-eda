<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Repositories;

use App\Shared\Contracts\ControlPlane\NodeIngestionGateReaderInterface;

interface NodeStatusRepositoryInterface extends NodeIngestionGateReaderInterface
{
    public function setStatus(string $nodeName, string $status): void;

    public function setMiddlewareEventsEnabled(string $nodeName, bool $enabled): void;

    /** @return array<string, array{status: string, updated_at: string, middleware_events_enabled: bool}> */
    public function getAllStatuses(): array;
}
