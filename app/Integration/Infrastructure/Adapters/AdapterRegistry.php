<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Adapters;

use App\Integration\Domain\Contracts\IntegrationAdapterInterface;
use InvalidArgumentException;

/**
 * Registry of adapter strategies by type (Plan_Integraciones Fase 3).
 */
final class AdapterRegistry
{
    /** @var array<string, IntegrationAdapterInterface> */
    private array $adapters = [];

    public function register(IntegrationAdapterInterface $adapter): void
    {
        $this->adapters[$adapter->type()] = $adapter;
    }

    public function get(string $type): IntegrationAdapterInterface
    {
        if (! isset($this->adapters[$type])) {
            throw new InvalidArgumentException("Unknown adapter type '{$type}'.");
        }

        return $this->adapters[$type];
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        return array_keys($this->adapters);
    }
}
