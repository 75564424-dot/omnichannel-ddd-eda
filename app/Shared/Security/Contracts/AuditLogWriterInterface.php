<?php

declare(strict_types=1);

namespace App\Shared\Security\Contracts;

interface AuditLogWriterInterface
{
    /**
     * @param array<string, mixed>|null $changes
     */
    public function record(
        string $action,
        string $entityType,
        string $entityId,
        ?array $changes,
        ?string $actorType = null,
        ?string $actorId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void;
}
