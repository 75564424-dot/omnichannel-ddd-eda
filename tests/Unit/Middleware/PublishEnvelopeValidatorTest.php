<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Application\Services\Publish\PublishEnvelopeValidator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PublishEnvelopeValidatorTest extends TestCase
{
    #[Test]
    public function validate_structure_accepts_minimal_valid_envelope(): void
    {
        (new PublishEnvelopeValidator())->validateStructure([
            'event_id'    => '11111111-1111-1111-1111-111111111111',
            'event_type'  => 'Order.Placed',
            'payload'     => ['sku' => 'A1'],
            'occurred_at' => now()->toIso8601String(),
        ]);

        $this->assertTrue(true);
    }

    #[Test]
    public function validate_structure_rejects_non_array_payload(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PublishEnvelopeValidator())->validateStructure([
            'event_id'    => '11111111-1111-1111-1111-111111111111',
            'event_type'  => 'Order.Placed',
            'payload'     => 'bad',
            'occurred_at' => now()->toIso8601String(),
        ]);
    }
}
