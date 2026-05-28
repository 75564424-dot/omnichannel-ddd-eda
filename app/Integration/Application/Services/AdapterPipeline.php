<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use App\Integration\Domain\Repositories\AdapterRepositoryInterface;
use App\Integration\Infrastructure\Adapters\AdapterRegistry;
use InvalidArgumentException;

/**
 * Runs adapter chain for an integration (DB adapters + config fallback).
 */
final class AdapterPipeline
{
    public function __construct(
        private readonly AdapterRepositoryInterface $adapterRepository,
        private readonly AdapterRegistry $registry,
    ) {}

    /**
     * @param array<string, mixed> $integrationConfig
     * @return array<string, mixed>
     */
    public function process(string $integrationId, array $payload, array $integrationConfig = []): array
    {
        $steps = $this->adapterRepository->listEnabledForIntegration($integrationId);

        if ($steps === []) {
            /** @var list<array<string, mixed>> $configSteps */
            $configSteps = $integrationConfig['adapters'] ?? [];
            foreach ($configSteps as $step) {
                $type = (string) ($step['type'] ?? '');
                $config = is_array($step['config'] ?? null) ? $step['config'] : [];
                $payload = $this->registry->get($type)->transform($payload, $config);
            }

            return $payload;
        }

        foreach ($steps as $step) {
            $payload = $this->registry->get($step['adapter_type'])->transform(
                $payload,
                $step['config'] ?? [],
            );
        }

        return $payload;
    }
}
