<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Adapters;

use App\Integration\Domain\Contracts\IntegrationAdapterInterface;

/**
 * Maps inbound field names to canonical event envelope fields.
 */
final class FieldMapAdapter implements IntegrationAdapterInterface
{
    public function type(): string
    {
        return 'field_map';
    }

    public function transform(array $payload, array $config = []): array
    {
        /** @var array<string, string> $map */
        $map = $config['map'] ?? [];
        if ($map === []) {
            return $payload;
        }

        $mapped = $payload;
        foreach ($map as $from => $to) {
            if (array_key_exists($from, $payload)) {
                $mapped[$to] = $payload[$from];
            }
        }

        return $mapped;
    }
}
