<?php

declare(strict_types=1);

namespace App\Middleware\Application\DTOs;

/**
 * Result of publishing an envelope to the bus (Plan_Resiliencia — idempotent publish).
 */
final readonly class PublishResult
{
    public function __construct(
        public int $entryId,
        public bool $idempotent,
    ) {}
}
