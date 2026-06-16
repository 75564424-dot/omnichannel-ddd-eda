<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet\Contracts;

interface LocalFleetProcessSupervisorInterface
{
    public function isRunning(string $envId, int $port): bool;

    public function ensureRunning(string $envId, int $port): bool;

    public function stop(string $envId, int $port): bool;
}

