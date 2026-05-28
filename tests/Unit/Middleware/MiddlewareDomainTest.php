<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Application\Services\EventSchemaRegistry;
use App\Middleware\Domain\ValueObjects\CorrelationContext;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class MiddlewareDomainTest extends TestCase
{
    #[Test]
    public function correlation_context_merges_body_and_headers(): void
    {
        $correlation = Uuid::uuid4()->toString();
        $causation = Uuid::uuid4()->toString();

        $context = CorrelationContext::fromHttp(
            ['event_id' => Uuid::uuid4()->toString()],
            [
                'x-correlation-id' => $correlation,
                'x-causation-id'   => $causation,
            ],
        );

        $this->assertSame($correlation, $context->correlationId);
        $this->assertSame($causation, $context->causationId);
    }

    #[Test]
    public function schema_registry_resolves_config_entry(): void
    {
        config()->set('eventbus.schema_registry', [
            'Platform.Test.Event' => [
                'path'           => config_path('schemas/platform_smoke_probe.json'),
                'event_version'  => 2,
                'schema_version' => '2026-05-01',
            ],
        ]);

        $resolved = app(EventSchemaRegistry::class)->resolve('Platform.Test.Event');

        $this->assertNotNull($resolved);
        $this->assertSame(2, $resolved['event_version']);
        $this->assertSame('2026-05-01', $resolved['schema_version']);
    }
}
