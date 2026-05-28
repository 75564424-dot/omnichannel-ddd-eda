<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\DatabaseInstanceTenantContext;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InstanceTenantContextTest extends TestCase
{
    #[Test]
    public function exposes_client_slug_and_name_from_config(): void
    {
        config([
            'platform.deployment_mode' => 'instance_per_client',
            'platform.client_slug'     => 'test-client',
            'platform.client_name'     => 'Test Client',
        ]);

        $ctx = new DatabaseInstanceTenantContext();

        $this->assertSame('test-client', $ctx->clientSlug());
        $this->assertSame('Test Client', $ctx->clientName());
        $this->assertSame('instance_per_client', $ctx->deploymentMode());
    }

    #[Test]
    public function log_context_includes_slug_and_deployment_mode(): void
    {
        config([
            'platform.deployment_mode' => 'instance_per_client',
            'platform.client_slug'     => 'acme',
            'platform.client_name'     => 'Acme',
        ]);

        $ctx = new DatabaseInstanceTenantContext();
        $log = $ctx->logContext();

        $this->assertSame('acme', $log['platform_client_slug']);
        $this->assertSame('instance_per_client', $log['deployment_mode']);
    }
}
