<?php

declare(strict_types=1);

namespace App\Middleware\Domain;

/**
 * Central registry of modules inferred from observed bus traffic.
 */
interface ModuleRegistry
{
    public function recordProducerObservation(string $logicalId, string $name, string $eventType): void;

    public function recordConsumerObservation(string $logicalId, string $name, string $eventType): void;

    /**
     * @return list<Module>
     */
    public function listModules(?string $typeFilter = null): array;
}
