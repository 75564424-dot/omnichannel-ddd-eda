<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

use App\Middleware\Domain\Entities\EventLogEntry;

interface EventLogRepositoryInterface
{
    public function append(EventLogEntry $entry): int;
}
