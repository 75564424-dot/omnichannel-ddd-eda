<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Repositories;

interface BusQueueAnalyticsRepositoryInterface
{
    /** @return array<string, int> origin label => count */
    public function countByOriginSince(\DateTimeInterface $since): array;

    /** @return array<string, int> consumer module name => count */
    public function countByConsumerSince(\DateTimeInterface $since): array;

    public function countPublishedSince(\DateTimeInterface $since): int;
}
