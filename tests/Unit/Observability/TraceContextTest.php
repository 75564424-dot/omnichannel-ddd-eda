<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Observability\Domain\ValueObjects\TraceContext;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class TraceContextTest extends TestCase
{
    #[Test]
    public function start_uses_correlation_id_as_trace_id_when_valid_uuid(): void
    {
        $correlation = Uuid::uuid4()->toString();

        $context = TraceContext::start($correlation);

        $this->assertSame($correlation, $context->traceId);
        $this->assertSame($correlation, $context->correlationId);
        $this->assertTrue(Uuid::isValid($context->spanId));
    }

    #[Test]
    public function start_generates_trace_id_when_correlation_is_missing(): void
    {
        $context = TraceContext::start(null);

        $this->assertTrue(Uuid::isValid($context->traceId));
        $this->assertNull($context->correlationId);
    }
}
