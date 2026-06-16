<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;
use App\Observability\Application\Services\Prometheus\FeedProjectionLagCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class FeedProjectionLagCalculatorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function average_lag_ms_returns_zero_when_table_is_empty(): void
    {
        $this->assertSame(0, (new FeedProjectionLagCalculator())->averageLagMs());
    }

    #[Test]
    public function average_lag_ms_computes_received_minus_occurred(): void
    {
        $occurred = now()->subSeconds(2);
        $received = now();

        EventFeedEntryModel::create([
            'event_uuid' => Uuid::uuid4()->toString(),
            'event_type' => 'Test.Event',
            'origin' => 'Test',
            'impact' => 'low',
            'status' => 'SUCCESS',
            'occurred_at' => $occurred,
            'received_at' => $received,
            'raw_payload' => ['probe' => true],
            'correlation_id' => Uuid::uuid4()->toString(),
        ]);

        $lag = (new FeedProjectionLagCalculator())->averageLagMs();

        $this->assertGreaterThanOrEqual(1900, $lag);
        $this->assertLessThanOrEqual(2100, $lag);
    }
}
