<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Repositories;

interface MetricsRepositoryInterface
{
    public function getValue(string $key): int;

    public function increment(string $key, int $by = 1): void;

    public function decrement(string $key, int $by = 1): void;

    public function set(string $key, int $value): void;

    public function getLastUpdated(string $key): string;
}
