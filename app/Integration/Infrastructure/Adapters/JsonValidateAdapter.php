<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Adapters;

use App\Integration\Domain\Contracts\IntegrationAdapterInterface;
use InvalidArgumentException;

/**
 * Validates required JSON fields on inbound webhook payloads.
 */
final class JsonValidateAdapter implements IntegrationAdapterInterface
{
    public function type(): string
    {
        return 'json_validate';
    }

    public function transform(array $payload, array $config = []): array
    {
        /** @var list<string> $required */
        $required = $config['required'] ?? [];
        foreach ($required as $field) {
            if (! array_key_exists($field, $payload) || $payload[$field] === null || $payload[$field] === '') {
                throw new InvalidArgumentException("Adapter json_validate: missing required field '{$field}'.");
            }
        }

        return $payload;
    }
}
