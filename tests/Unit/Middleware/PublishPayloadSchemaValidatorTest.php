<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Application\Services\PublishPayloadSchemaValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PublishPayloadSchemaValidatorTest extends TestCase
{
    #[Test]
    public function skips_validation_when_disabled(): void
    {
        config()->set('eventbus.schema_validation_enabled', false);

        app(PublishPayloadSchemaValidator::class)->validate('Any.Type', ['foo' => 'bar']);

        $this->assertTrue(true);
    }

    #[Test]
    public function validates_payload_against_configured_schema(): void
    {
        config()->set('eventbus.schema_validation_enabled', true);
        config()->set('eventbus.publish_schemas', [
            'Platform.Smoke.Probe' => config_path('schemas/platform_smoke_probe.json'),
        ]);

        app(PublishPayloadSchemaValidator::class)->validate('Platform.Smoke.Probe', [
            'event_id'    => '00000000-0000-4000-8000-000000000001',
            'event'       => 'Platform.Smoke.Probe',
            'occurred_at' => now()->toIso8601String(),
        ]);

        $this->assertTrue(true);
    }

    #[Test]
    public function throws_when_schema_validation_fails(): void
    {
        config()->set('eventbus.schema_validation_enabled', true);
        config()->set('eventbus.publish_schemas', [
            'Platform.Smoke.Probe' => config_path('schemas/platform_smoke_probe.json'),
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(PublishPayloadSchemaValidator::class)->validate('Platform.Smoke.Probe', [
            'event' => 'missing-required-fields',
        ]);
    }
}
