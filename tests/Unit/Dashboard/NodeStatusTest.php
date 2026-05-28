<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard;

use App\Dashboard\Domain\ValueObjects\NodeStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NodeStatusTest extends TestCase
{
    #[Test]
    public function known_states_round_trip(): void
    {
        $this->assertTrue(NodeStatus::online()->isHealthy());
        $this->assertTrue(NodeStatus::syncing()->isHealthy());
        $this->assertFalse(NodeStatus::hiLoad()->isOnline());
        $this->assertFalse(NodeStatus::offline()->isHealthy());
    }

    #[Test]
    public function unknown_label_maps_to_offline(): void
    {
        $this->assertSame(NodeStatus::OFFLINE, (new NodeStatus('WEIRD'))->value());
    }
}
