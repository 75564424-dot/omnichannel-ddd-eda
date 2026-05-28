<?php

declare(strict_types=1);

namespace App\Shared\Platform\Contracts;

/**
 * Resolves the current instance tenant (one row in `tenants` = this deployment silo).
 *
 * @see docs/production/ADR_001_instancia_por_cliente.md
 */
interface InstanceTenantContextInterface
{
    public function deploymentMode(): string;

    public function clientSlug(): string;

    public function clientName(): string;

    /** UUID of the instance tenant row, or null if not seeded yet. */
    public function tenantId(): ?string;

    /** Tenant bound to PLATFORM_CLIENT_SLUG (ignores portal session). */
    public function configuredInstanceTenantId(): ?string;

    public function allowsMultiTenantPortalLogin(): bool;

    public function bindPortalTenantFromSession(?string $tenantId): void;

    /** @return array<string, mixed> Context for structured logs. */
    public function logContext(): array;
}
