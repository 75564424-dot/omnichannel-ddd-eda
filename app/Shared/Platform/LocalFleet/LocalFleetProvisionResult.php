<?php

declare(strict_types=1);

namespace App\Shared\Platform\LocalFleet;

final class LocalFleetProvisionResult
{
    /**
     * @param array<string, mixed> $instance
     * @param array<string, mixed> $localInstance
     */
    public function __construct(
        public readonly bool $provisioned,
        public readonly array $instance,
        public readonly array $localInstance,
        public readonly ?string $message = null,
    ) {}

    public function appUrl(): string
    {
        return (string) ($this->localInstance['app_url'] ?? '');
    }
}
