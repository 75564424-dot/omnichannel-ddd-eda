<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

use App\Middleware\Domain\Entities\QueueEntry;
use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;

interface BusMetricsRepositoryInterface
{
    public function saveSnapshot(BusMetricsSnapshot $snapshot): void;

    public function getLatest(): ?BusMetricsSnapshot;
}
